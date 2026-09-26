<div id="gps" class="page">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">จัดการอุปกรณ์ GPS</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ผูกอุปกรณ์ GPS (ESP32) เข้ากับรถแต่ละคัน โดย 1 อุปกรณ์ต่อ 1 คัน อุปกรณ์ใหม่จะขึ้นในตารางเองเมื่อส่งพิกัดครั้งแรก</p>
        </div>
        <div class="flex gap-2">
            <button onclick="loadGpsDevices()" class="bg-white border border-gray-300 hover:bg-gray-50 text-slate-700 px-4 py-2 rounded-xl transition shadow-xs text-xs font-bold flex items-center gap-1.5 active:scale-95">
                <i class="fas fa-sync-alt"></i> รีเฟรช
            </button>
            <button onclick="addGpsDevice()" class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-xl transition shadow-xs text-xs font-bold flex items-center gap-1.5 active:scale-95">
                <i class="fas fa-plus"></i> เพิ่มอุปกรณ์
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full text-left min-w-[900px]">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                <tr>
                    <th class="p-4">รหัสอุปกรณ์</th>
                    <th class="p-4">ชื่อเรียก</th>
                    <th class="p-4 min-w-[200px]">ติดตั้งบนรถ</th>
                    <th class="p-4">ตำแหน่งล่าสุด</th>
                    <th class="p-4 text-center">สถานะ</th>
                    <th class="p-4 text-center w-24">ลบ</th>
                </tr>
            </thead>
            <tbody id="gpsDeviceTable" class="text-sm divide-y divide-gray-100">
                <tr><td colspan="6" class="p-6 text-center text-slate-400">กำลังโหลด...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
let gpsDevicesCache = [];
let gpsRefreshTimer = null;

function gpsEscape(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function gpsFormatAge(seconds) {
    if (seconds === null || seconds === undefined) return 'ยังไม่เคยส่งข้อมูล';
    if (seconds < 60) return `${seconds} วินาทีที่แล้ว`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)} นาทีที่แล้ว`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} ชั่วโมงที่แล้ว`;
    return `${Math.floor(seconds / 86400)} วันที่แล้ว`;
}

function gpsNotify(icon, title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon, title, text, confirmButtonColor: '#db2777' });
    } else {
        alert(title + (text ? '\n' + text : ''));
    }
}

async function gpsRequest(url, options = {}) {
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
        ...options,
    });
    let body = {};
    try { body = await res.json(); } catch (e) {}
    if (!res.ok) {
        const firstError = body.errors ? Object.values(body.errors)[0][0] : null;
        throw new Error(body.message && !firstError ? body.message : (firstError || body.message || `HTTP ${res.status}`));
    }
    return body;
}

function gpsVehicleOptions(device) {
    const trams = (typeof getStorage === 'function' && typeof defaultTrams !== 'undefined')
        ? getStorage('yru_trams_v18', defaultTrams) : [];
    const takenBy = {};
    gpsDevicesCache.forEach(d => { if (d.vehicle_id) takenBy[d.vehicle_id] = d.device_id; });

    let html = `<option value="">— ยังไม่ติดตั้ง —</option>`;
    const ids = new Set();
    trams.forEach(t => {
        ids.add(t.id);
        const owner = takenBy[t.id];
        const disabled = owner && owner !== device.device_id;
        const label = `${t.id}${t.plate ? ' · ' + t.plate : ''}${disabled ? ' (ใช้กับ ' + owner + ')' : ''}`;
        html += `<option value="${gpsEscape(t.id)}" ${device.vehicle_id === t.id ? 'selected' : ''} ${disabled ? 'disabled' : ''}>${gpsEscape(label)}</option>`;
    });
    // รถที่ผูกไว้แต่ไม่อยู่ในรายการรถแล้ว (เช่น ถูกลบ) ให้ยังแสดงอยู่
    if (device.vehicle_id && !ids.has(device.vehicle_id)) {
        html += `<option value="${gpsEscape(device.vehicle_id)}" selected>${gpsEscape(device.vehicle_id)} (ไม่พบในรายการรถ)</option>`;
    }
    return html;
}

function renderGpsDevices() {
    const tbody = document.getElementById('gpsDeviceTable');
    if (!tbody) return;

    if (gpsDevicesCache.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-slate-400">ยังไม่มีอุปกรณ์ GPS — เปิด ESP32 ให้ส่งพิกัดเข้ามา หรือกด "เพิ่มอุปกรณ์"</td></tr>`;
        return;
    }

    tbody.innerHTML = gpsDevicesCache.map(d => {
        const hasPos = d.lat !== null && d.lng !== null;
        const pos = hasPos
            ? `<a href="https://maps.google.com/?q=${d.lat},${d.lng}" target="_blank" rel="noopener" class="text-pink-600 hover:underline font-mono text-xs">${Number(d.lat).toFixed(6)}, ${Number(d.lng).toFixed(6)}</a>
               <div class="text-[11px] text-slate-400 mt-0.5">${d.speed_kmh !== null ? Number(d.speed_kmh).toFixed(1) + ' กม./ชม. · ' : ''}${d.satellites !== null ? d.satellites + ' ดาวเทียม' : ''}</div>`
            : `<span class="text-slate-400 text-xs">—</span>`;
        const status = d.online
            ? `<span class="inline-flex items-center gap-1.5 font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 px-2.5 py-0.5 rounded-full text-xs"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> ออนไลน์</span>`
            : `<span class="inline-flex items-center gap-1.5 font-bold text-slate-500 bg-slate-100 border border-slate-300 px-2.5 py-0.5 rounded-full text-xs"><span class="w-2 h-2 rounded-full bg-slate-400"></span> ออฟไลน์</span>`;
        const id = gpsEscape(d.device_id);
        return `
            <tr class="hover:bg-gray-50">
                <td class="p-4 font-mono font-bold text-slate-800">${id}</td>
                <td class="p-4">
                    <button onclick="renameGpsDevice('${id}')" class="text-slate-700 hover:text-pink-600 text-left" title="แก้ไขชื่อ">
                        ${d.name ? gpsEscape(d.name) : '<span class="text-slate-400">ตั้งชื่อ</span>'} <i class="fas fa-pen text-[10px] ml-1 text-slate-400"></i>
                    </button>
                </td>
                <td class="p-4">
                    <select onchange="assignGpsDevice('${id}', this)" class="border border-gray-300 p-1.5 rounded-lg text-sm bg-white focus:ring-2 focus:ring-pink-400 outline-none w-full">
                        ${gpsVehicleOptions(d)}
                    </select>
                </td>
                <td class="p-4">${pos}</td>
                <td class="p-4 text-center">${status}<div class="text-[11px] text-slate-400 mt-1">${gpsFormatAge(d.age_seconds)}</div></td>
                <td class="p-4 text-center">
                    <button onclick="deleteGpsDevice('${id}')" class="text-red-500 hover:text-red-700 p-2" title="ลบอุปกรณ์"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
    }).join('');
}

async function loadGpsDevices() {
    try {
        const body = await gpsRequest('/api/gps/devices');
        gpsDevicesCache = body.devices || [];
        renderGpsDevices();
    } catch (e) {
        const tbody = document.getElementById('gpsDeviceTable');
        if (tbody) tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-red-500">โหลดข้อมูล GPS ไม่สำเร็จ: ${gpsEscape(e.message)}</td></tr>`;
    }
}

async function assignGpsDevice(deviceId, selectEl) {
    const vehicleId = selectEl.value || null;
    selectEl.disabled = true;
    try {
        await gpsRequest(`/api/gps/devices/${encodeURIComponent(deviceId)}/assign`, {
            method: 'POST',
            body: JSON.stringify({ vehicle_id: vehicleId }),
        });
        gpsNotify('success', vehicleId ? `ติดตั้ง ${deviceId} บนรถ ${vehicleId} แล้ว` : `ยกเลิกการติดตั้ง ${deviceId} แล้ว`);
    } catch (e) {
        gpsNotify('error', 'บันทึกไม่สำเร็จ', e.message);
    }
    await loadGpsDevices();
}

async function renameGpsDevice(deviceId) {
    const device = gpsDevicesCache.find(d => d.device_id === deviceId);
    let name = null;
    if (typeof Swal !== 'undefined') {
        const r = await Swal.fire({ title: `ชื่อเรียกของ ${deviceId}`, input: 'text', inputValue: device?.name || '', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#db2777' });
        if (!r.isConfirmed) return;
        name = r.value;
    } else {
        name = prompt(`ชื่อเรียกของ ${deviceId}`, device?.name || '');
        if (name === null) return;
    }
    try {
        await gpsRequest(`/api/gps/devices/${encodeURIComponent(deviceId)}/assign`, {
            method: 'POST',
            body: JSON.stringify({ vehicle_id: device?.vehicle_id || null, name: name || null }),
        });
    } catch (e) {
        gpsNotify('error', 'บันทึกไม่สำเร็จ', e.message);
    }
    await loadGpsDevices();
}

async function addGpsDevice() {
    let deviceId = null;
    if (typeof Swal !== 'undefined') {
        const r = await Swal.fire({ title: 'เพิ่มอุปกรณ์ GPS', text: 'รหัสอุปกรณ์ดูได้จาก Serial Monitor ของ ESP32 ตอนเปิดเครื่อง (เช่น ESP32-A1B2C3)', input: 'text', inputPlaceholder: 'ESP32-XXXXXX', showCancelButton: true, confirmButtonText: 'เพิ่ม', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#db2777' });
        if (!r.isConfirmed) return;
        deviceId = r.value;
    } else {
        deviceId = prompt('รหัสอุปกรณ์ (เช่น ESP32-A1B2C3)');
    }
    if (!deviceId) return;
    try {
        await gpsRequest('/api/gps/devices', { method: 'POST', body: JSON.stringify({ device_id: deviceId.trim() }) });
    } catch (e) {
        gpsNotify('error', 'เพิ่มอุปกรณ์ไม่สำเร็จ', e.message);
    }
    await loadGpsDevices();
}

async function deleteGpsDevice(deviceId) {
    let ok = false;
    if (typeof Swal !== 'undefined') {
        const r = await Swal.fire({ icon: 'warning', title: `ลบอุปกรณ์ ${deviceId}?`, text: 'ถ้า ESP32 ยังส่งข้อมูลอยู่ อุปกรณ์จะกลับมาในรายการอีกครั้ง แต่จะไม่ได้ผูกกับรถ', showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#dc2626' });
        ok = r.isConfirmed;
    } else {
        ok = confirm(`ลบอุปกรณ์ ${deviceId}?`);
    }
    if (!ok) return;
    try {
        await gpsRequest(`/api/gps/devices/${encodeURIComponent(deviceId)}`, { method: 'DELETE' });
    } catch (e) {
        gpsNotify('error', 'ลบไม่สำเร็จ', e.message);
    }
    await loadGpsDevices();
}

// เรียกจาก showPage('gps'): โหลดทันที แล้วรีเฟรชทุก 10 วินาทีระหว่างที่หน้านี้เปิดอยู่
function initGpsPage() {
    loadGpsDevices();
    if (gpsRefreshTimer) clearInterval(gpsRefreshTimer);
    gpsRefreshTimer = setInterval(() => {
        const page = document.getElementById('gps');
        if (!page || page.style.display === 'none') {
            clearInterval(gpsRefreshTimer);
            gpsRefreshTimer = null;
            return;
        }
        // ไม่รีเฟรชระหว่างที่แอดมินกำลังเลือกรถใน dropdown
        if (document.activeElement && document.activeElement.tagName === 'SELECT' && page.contains(document.activeElement)) return;
        if (!document.hidden) loadGpsDevices();
    }, 10000);
}
</script>
