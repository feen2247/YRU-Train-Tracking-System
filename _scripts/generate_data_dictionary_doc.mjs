

import fs from 'fs';
import path from 'path';
import {
    Document,
    Packer,
    Paragraph,
    TextRun,
    Table,
    TableRow,
    TableCell,
    WidthType,
    AlignmentType,
    VerticalAlign,
    BorderStyle,
    HeadingLevel,
    ShadingType
} from 'docx';

const fontName = 'TH Sarabun New';

// จัดหมวดหมู่ตารางให้เป็นระเบียบตามกลุ่มงาน โดยคงชื่อฟิลด์และชนิดข้อมูลเดิมไว้ 100%
const modulesData = [
    {
        moduleTitle: 'หมวดที่ 1: การจัดการผู้ใช้งานและสิทธิ์การเข้าถึง (User Management & Access Control)',
        tables: [
            {
                num: '3.1',
                title: 'ตารางที่ 3.1 ตาราง Users (ข้อมูลผู้ใช้งานระบบ)',
                tableName: 'users',
                rows: [
                    { key: 'PK', name: 'user_id', type: 'VARCHAR', size: '10', note: 'รหัสประจำตัวผู้ใช้งาน (Primary Key)' },
                    { key: '', name: 'employee_id', type: 'VARCHAR', size: '255', note: 'รหัสพนักงาน หรือ รหัสนักศึกษา (Unique)' },
                    { key: '', name: 'username', type: 'VARCHAR', size: '100', note: 'ชื่อบัญชีผู้ใช้สำหรับเข้าสู่ระบบ (Unique)' },
                    { key: '', name: 'password', type: 'VARCHAR', size: '255', note: 'รหัสผ่านสำหรับเข้าสู่ระบบ (เข้ารหัสความปลอดภัยแบบ Hash)' },
                    { key: '', name: 'prefix', type: 'VARCHAR', size: '50', note: 'คำนำหน้าชื่อ (เช่น นาย, นาง, นางสาว)' },
                    { key: '', name: 'first_name', type: 'VARCHAR', size: '255', note: 'ชื่อจริงของผู้ใช้งาน' },
                    { key: '', name: 'last_name', type: 'VARCHAR', size: '255', note: 'นามสกุลของผู้ใช้งาน' },
                    { key: '', name: 'name', type: 'VARCHAR', size: '100', note: 'ชื่อ-นามสกุลแบบรวม' },
                    { key: '', name: 'email', type: 'VARCHAR', size: '100', note: 'ที่อยู่อีเมลสำหรับติดต่อและรับการแจ้งเตือน' },
                    { key: '', name: 'phone_number', type: 'VARCHAR', size: '255', note: 'หมายเลขโทรศัพท์ติดต่อ' },
                    { key: '', name: 'user_role', type: 'VARCHAR', size: '50', note: 'บทบาทของผู้ใช้ (Administrator, Driver, Supervisor, Mechanic, Passenger)' },
                    { key: '', name: 'usage_rights', type: 'VARCHAR', size: '50', note: 'สิทธิ์การเข้าถึงเมนูและการทำงานในระบบ' },
                    { key: '', name: 'status', type: 'VARCHAR', size: '255', note: 'สถานะการใช้งานบัญชี (เช่น ใช้งาน, ระงับการใช้งาน)' },
                    { key: '', name: 'remark', type: 'TEXT', size: '-', note: 'หมายเหตุเพิ่มเติมเกี่ยวกับผู้ใช้งาน' },
                    { key: '', name: 'otp_code', type: 'VARCHAR', size: '6', note: 'รหัส OTP 6 หลักสำหรับยืนยันตัวตน' },
                    { key: '', name: 'otp_expires_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่รหัส OTP หมดอายุ' },
                    { key: '', name: 'email_verified_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่ยืนยันที่อยู่อีเมลสำเร็จ' },
                    { key: '', name: 'remember_token', type: 'VARCHAR', size: '100', note: 'โทเค็นสำหรับจดจำสถานะการเข้าสู่ระบบ' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.2',
                title: 'ตารางที่ 3.2 ตาราง Role_Permissions (ข้อมูลสิทธิ์การใช้งานตามบทบาท)',
                tableName: 'role_permissions',
                rows: [
                    { key: 'PK', name: 'id', type: 'BIGINT', size: '-', note: 'รหัสรายการกำหนดสิทธิ์ (Auto Increment)' },
                    { key: '', name: 'role', type: 'VARCHAR', size: '50', note: 'ชื่อบทบาทผู้ใช้งาน (เช่น admin, driver, mechanic, passenger)' },
                    { key: '', name: 'permission', type: 'VARCHAR', size: '100', note: 'ชื่อสิทธิ์การเข้าถึงฟังก์ชันงาน (เช่น view_tracking, manage_trains)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            }
        ]
    },
    {
        moduleTitle: 'หมวดที่ 2: การจัดการรถรางไฟฟ้า เส้นทาง และตารางเวลาการเดินรถ (Train, Routes & Timetable Management)',
        tables: [
            {
                num: '3.3',
                title: 'ตารางที่ 3.3 ตาราง Electric_Trains (ข้อมูลรถรางไฟฟ้า)',
                tableName: 'electric_trains',
                rows: [
                    { key: 'PK', name: 'skytrain_code', type: 'VARCHAR', size: '10', note: 'รหัสประจำรถรางไฟฟ้า (Primary Key เช่น TR-01)' },
                    { key: '', name: 'car_number', type: 'VARCHAR', size: '10', note: 'หมายเลขประจำตัวรถ หรือ ป้ายทะเบียนรถ' },
                    { key: '', name: 'electric_train_type', type: 'VARCHAR', size: '50', note: 'ประเภทของรถรางไฟฟ้า (เช่น EV Tram 14 ที่นั่ง)' },
                    { key: '', name: 'car_status', type: 'VARCHAR', size: '20', note: 'สถานะการทำงานของรถ (Active, Maintenance, Inactive)' },
                    { key: 'FK', name: 'route_code', type: 'VARCHAR', size: '10', note: 'รหัสเส้นทางที่รถประจำการอยู่ (อ้างอิง routes.route_code)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.4',
                title: 'ตารางที่ 3.4 ตาราง Routes (ข้อมูลเส้นทางการเดินรถ)',
                tableName: 'routes',
                rows: [
                    { key: 'PK', name: 'route_code', type: 'VARCHAR', size: '10', note: 'รหัสเส้นทางการเดินรถ (Primary Key เช่น R01, R02)' },
                    { key: '', name: 'route_name', type: 'VARCHAR', size: '100', note: 'ชื่อสายเส้นทางการเดินรถ (เช่น สายสีชมพู, สายสีฟ้า)' },
                    { key: '', name: 'route_color', type: 'VARCHAR', size: '50', note: 'รหัสสีประจำเส้นทางสำหรับแสดงบนแผนที่ (เช่น #ec4899)' },
                    { key: '', name: 'route_details', type: 'VARCHAR', size: '255', note: 'รายละเอียดและคำอธิบายเส้นทางการเดินรถ' },
                    { key: '', name: 'polyline_data', type: 'LONGTEXT', size: '-', note: 'ข้อมูลชุดพิกัดเส้นทางการเดินรถบนแผนที่ (GeoJSON / Array coordinates)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.5',
                title: 'ตารางที่ 3.5 ตาราง Stations (ข้อมูลสถานีและจุดจอดรถ)',
                tableName: 'stations',
                rows: [
                    { key: 'PK', name: 'parking_spot_code', type: 'VARCHAR', size: '10', note: 'รหัสจุดจอด หรือ สถานี (Primary Key)' },
                    { key: 'FK', name: 'route_code', type: 'VARCHAR', size: '10', note: 'รหัสเส้นทางที่จุดจอดสังกัด (อ้างอิง routes.route_code)' },
                    { key: '', name: 'parking_spot_name', type: 'VARCHAR', size: '100', note: 'ชื่อจุดจอดรถ หรือ ชื่อสถานี' },
                    { key: '', name: 'order_of_parking_spots', type: 'INT', size: '-', note: 'ลำดับของจุดจอดในเส้นทางการเดินรถ' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.6',
                title: 'ตารางที่ 3.6 ตาราง Route_Stops (ข้อมูลลำดับจุดจอดประจำเส้นทาง)',
                tableName: 'route_stops',
                rows: [
                    { key: 'PK', name: 'id', type: 'BIGINT', size: '-', note: 'รหัสรายการลำดับจุดจอด (Auto Increment)' },
                    { key: 'FK', name: 'route_code', type: 'VARCHAR', size: '10', note: 'รหัสเส้นทางการเดินรถ (อ้างอิง routes.route_code)' },
                    { key: '', name: 'parking_spot_code', type: 'VARCHAR', size: '255', note: 'รหัสหรือชื่อจุดจอดรถ' },
                    { key: '', name: 'stop_order', type: 'INT', size: '-', note: 'ลำดับการจอดตามลำดับในเส้นทาง (1, 2, 3...)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.7',
                title: 'ตารางที่ 3.7 ตาราง Schedules (ข้อมูลตารางเวลาการเดินรถ)',
                tableName: 'schedules',
                rows: [
                    { key: 'PK', name: 'timetable_code', type: 'VARCHAR', size: '10', note: 'รหัสตารางเวลาการเดินรถ (Primary Key)' },
                    { key: 'FK', name: 'skytrain_code', type: 'VARCHAR', size: '10', note: 'รหัสรถรางไฟฟ้าที่วิ่งในรอบ (อ้างอิง electric_trains.skytrain_code)' },
                    { key: 'FK', name: 'driver_id', type: 'VARCHAR', size: '10', note: 'รหัสพนักงานขับรถประจำรอบ (อ้างอิง users.user_id)' },
                    { key: 'FK', name: 'route_code', type: 'VARCHAR', size: '10', note: 'รหัสเส้นทางการเดินรถ (อ้างอิง routes.route_code)' },
                    { key: '', name: 'departure_time', type: 'TIME', size: '-', note: 'เวลาออกจากจุดเริ่มต้นการเดินรถ' },
                    { key: '', name: 'arrival_time', type: 'TIME', size: '-', note: 'เวลาถึงจุดหมายปลายทาง' },
                    { key: '', name: 'date', type: 'DATE', size: '-', note: 'วันที่ของรอบการเดินรถ' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            }
        ]
    },
    {
        moduleTitle: 'หมวดที่ 3: ระบบติดตามพิกัดตำแหน่งและประวัติการเดินรถ (Real-time GPS Tracking & Travel History)',
        tables: [
            {
                num: '3.8',
                title: 'ตารางที่ 3.8 ตาราง Train_Locations (ข้อมูลพิกัดตำแหน่งรถแบบเรียลไทม์)',
                tableName: 'train_locations',
                rows: [
                    { key: 'PK', name: 'position_code', type: 'INT', size: '-', note: 'รหัสบันทึกตำแหน่ง (Primary Key Auto Increment)' },
                    { key: 'FK', name: 'skytrain_code', type: 'VARCHAR', size: '10', note: 'รหัสรถรางไฟฟ้าที่ส่งพิกัด (อ้างอิง electric_trains.skytrain_code)' },
                    { key: '', name: 'latitude', type: 'DECIMAL', size: '10,6', note: 'ค่าพิกัดละติจูด (Latitude) ปัจจุบัน' },
                    { key: '', name: 'longitude', type: 'DECIMAL', size: '10,6', note: 'ค่าพิกัดลองจิจูด (Longitude) ปัจจุบัน' },
                    { key: '', name: 'recorded_time', type: 'DATETIME', size: '-', note: 'วันและเวลาที่ระบบ GPS บันทึกพิกัด' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.9',
                title: 'ตารางที่ 3.9 ตาราง Travel_Histories (ข้อมูลประวัติและสถิติการเดินรถ)',
                tableName: 'travel_histories',
                rows: [
                    { key: 'PK', name: 'id', type: 'BIGINT', size: '-', note: 'รหัสประวัติการเดินทาง (Auto Increment)' },
                    { key: 'FK', name: 'skytrain_code', type: 'VARCHAR', size: '10', note: 'รหัสรถรางไฟฟ้าที่ออกวิ่ง (อ้างอิง electric_trains.skytrain_code)' },
                    { key: 'FK', name: 'driver_id', type: 'VARCHAR', size: '10', note: 'รหัสพนักงานขับรถ (อ้างอิง users.user_id)' },
                    { key: 'FK', name: 'route_code', type: 'VARCHAR', size: '10', note: 'รหัสเส้นทางเดินรถ (อ้างอิง routes.route_code)' },
                    { key: '', name: 'start_time', type: 'DATETIME', size: '-', note: 'วันและเวลาเริ่มต้นรอบการวิ่งรถ' },
                    { key: '', name: 'end_time', type: 'DATETIME', size: '-', note: 'วันและเวลาสิ้นสุดรอบการวิ่งรถ' },
                    { key: '', name: 'distance_km', type: 'DECIMAL', size: '8,2', note: 'ระยะทางสะสมรวมที่วิ่งได้ในรอบ (กิโลเมตร)' },
                    { key: '', name: 'travel_status', type: 'VARCHAR', size: '50', note: 'สถานะการเดินทาง (เช่น Running, Completed, Interrupted)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            }
        ]
    },
    {
        moduleTitle: 'หมวดที่ 4: ระบบการแจ้งซ่อมบำรุงและเอกสารขออนุมัติงบประมาณ (Maintenance Management & Workflow)',
        tables: [
            {
                num: '3.10',
                title: 'ตารางที่ 3.10 ตาราง Maintenance_Requests (ข้อมูลใบแจ้งซ่อมและใบเสนอราคา 5 ขั้นตอน)',
                tableName: 'maintenance_requests',
                rows: [
                    { key: 'PK', name: 'id', type: 'BIGINT', size: '-', note: 'รหัสรายการเอกสารแจ้งซ่อม (Primary Key Auto Increment)' },
                    { key: '', name: 'ticket_no', type: 'VARCHAR', size: '50', note: 'เลขที่เอกสารใบแจ้งซ่อม (Unique เช่น REQ-202609-001)' },
                    { key: '', name: 'doc_date', type: 'VARCHAR', size: '10', note: 'วันที่ออกเอกสารใบแจ้งซ่อม' },
                    { key: '', name: 'doc_month', type: 'VARCHAR', size: '30', note: 'เดือนที่ออกเอกสารใบแจ้งซ่อม' },
                    { key: '', name: 'doc_year', type: 'VARCHAR', size: '10', note: 'ปี พ.ศ. ที่ออกเอกสารใบแจ้งซ่อม' },
                    { key: '', name: 'driver_id', type: 'VARCHAR', size: '50', note: 'รหัสผู้แจ้งซ่อม หรือ พนักงานขับรถ' },
                    { key: '', name: 'driver_name', type: 'VARCHAR', size: '255', note: 'ชื่อ-นามสกุลของผู้แจ้งซ่อม' },
                    { key: '', name: 'car_id', type: 'VARCHAR', size: '50', note: 'รหัสรถรางไฟฟ้าที่แจ้งซ่อม' },
                    { key: '', name: 'license_plate', type: 'VARCHAR', size: '50', note: 'หมายเลขประจำตัวรถ หรือ ทะเบียนรถ' },
                    { key: '', name: 'brand', type: 'VARCHAR', size: '100', note: 'ยี่ห้อของรถรางไฟฟ้า (เช่น YRU EV)' },
                    { key: '', name: 'model', type: 'VARCHAR', size: '100', note: 'รุ่นของรถรางไฟฟ้า (เช่น Tram Electric)' },
                    { key: '', name: 'mileage', type: 'INT', size: '-', note: 'เลขระยะทางสะสมบนหน้าปัดรถ (กิโลเมตร)' },
                    { key: '', name: 'issues', type: 'TEXT', size: '-', note: 'รายการอาการชำรุดที่ตรวจพบ (จัดเก็บในรูปแบบ JSON Array)' },
                    { key: '', name: 'driver_signature', type: 'VARCHAR', size: '255', note: 'ลายมือชื่อดิจิทัลของผู้แจ้งซ่อม (Data URL / Signature)' },
                    { key: '', name: 'urgency', type: 'VARCHAR', size: '50', note: 'ระดับความเร่งด่วนของการซ่อม (normal, urgent, critical)' },
                    { key: '', name: 'supervisor_id', type: 'VARCHAR', size: '50', note: 'รหัสหัวหน้างานผู้ตรวจประเมิน' },
                    { key: '', name: 'supervisor_name', type: 'VARCHAR', size: '255', note: 'ชื่อ-นามสกุล หัวหน้างานผู้ตรวจประเมิน' },
                    { key: '', name: 'supervisor_notes', type: 'TEXT', size: '-', note: 'ความเห็นและข้อเสนอแนะของหัวหน้างาน' },
                    { key: '', name: 'supervisor_verified_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่หัวหน้างานตรวจรับรอง' },
                    { key: '', name: 'garage_name', type: 'VARCHAR', size: '255', note: 'ชื่ออู่ หรือ ร้านซ่อมภายนอกที่เสนอราคา' },
                    { key: '', name: 'garage_manager', type: 'VARCHAR', size: '255', note: 'ชื่อผู้จัดการอู่ซ่อม' },
                    { key: '', name: 'mechanic_name', type: 'VARCHAR', size: '255', note: 'ชื่อช่างผู้ประเมินราคา' },
                    { key: '', name: 'garage_to', type: 'VARCHAR', size: '255', note: 'ชื่อผู้รับใบเสนอราคา (มหาวิทยาลัยราชภัฏยะลา)' },
                    { key: '', name: 'garage_project', type: 'VARCHAR', size: '500', note: 'ชื่อโครงการ หรือ วัตถุประสงค์การซ่อมบำรุง' },
                    { key: '', name: 'quotation_no', type: 'VARCHAR', size: '50', note: 'เลขที่ใบเสนอราคาของอู่ซ่อม' },
                    { key: '', name: 'quotation_date', type: 'VARCHAR', size: '30', note: 'วันที่ในเอกสารใบเสนอราคา' },
                    { key: '', name: 'quotation_items', type: 'TEXT', size: '-', note: 'รายการอะไหล่และค่าแรงที่เสนอราคา (JSON Array)' },
                    { key: '', name: 'subtotal', type: 'DECIMAL', size: '12,2', note: 'จำนวนเงินรวมก่อนภาษีมูลค่าเพิ่ม (บาท)' },
                    { key: '', name: 'vat', type: 'DECIMAL', size: '12,2', note: 'ภาษีมูลค่าเพิ่ม 7% (บาท)' },
                    { key: '', name: 'parts_cost', type: 'DECIMAL', size: '10,2', note: 'ยอดรวมค่าอะไหล่ทั้งหมด (บาท)' },
                    { key: '', name: 'labor_cost', type: 'DECIMAL', size: '10,2', note: 'ยอดรวมค่าบริการและค่าแรงช่าง (บาท)' },
                    { key: '', name: 'total_cost', type: 'DECIMAL', size: '10,2', note: 'ยอดรวมค่าใช้จ่ายสุทธิทั้งหมด (บาท)' },
                    { key: '', name: 'thai_baht_text', type: 'VARCHAR', size: '500', note: 'จำนวนเงินรวมสุทธิเป็นตัวอักษรภาษาไทย' },
                    { key: '', name: 'estimated_days', type: 'INT', size: '-', note: 'จำนวนวันที่คาดว่าจะดำเนินการซ่อมแล้วเสร็จ' },
                    { key: '', name: 'quotation_doc_url', type: 'VARCHAR', size: '500', note: 'ลิงก์ไฟล์เอกสารแนบใบเสนอราคา' },
                    { key: '', name: 'director_opinion', type: 'VARCHAR', size: '50', note: 'ผลการพิจารณาของผู้อนุมัติ (approved / rejected)' },
                    { key: '', name: 'budget_type', type: 'VARCHAR', size: '50', note: 'ประเภทงบประมาณที่ใช้ (government_budget / revenue_budget)' },
                    { key: '', name: 'revenue_budget_source', type: 'VARCHAR', size: '255', note: 'แหล่งเงินงบประมาณรายได้ (กรณีใช้งบรายได้)' },
                    { key: '', name: 'director_name', type: 'VARCHAR', size: '255', note: 'ชื่อ-นามสกุล ผู้มีอำนาจลงนามอนุมัติ' },
                    { key: '', name: 'director_signed_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่ลงนามอนุมัติ' },
                    { key: '', name: 'director_remarks', type: 'TEXT', size: '-', note: 'ข้อสั่งการหรือหมายเหตุเพิ่มเติมของผู้อนุมัติ' },
                    { key: '', name: 'receiver_name', type: 'VARCHAR', size: '255', note: 'ชื่อ-นามสกุล ผู้ตรวจรับพัสดุและงานซ่อม' },
                    { key: '', name: 'completed_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่ส่งมอบและตรวจรับงานซ่อมแล้วเสร็จ' },
                    { key: '', name: 'archive_no', type: 'VARCHAR', size: '50', note: 'เลขที่จัดเก็บเอกสารเข้าแฟ้มสารบรรณ' },
                    { key: '', name: 'archive_date', type: 'VARCHAR', size: '20', note: 'วันที่จัดเก็บเอกสารเข้าแฟ้ม' },
                    { key: '', name: 'receipt_doc_url', type: 'VARCHAR', size: '500', note: 'ลิงก์ไฟล์ใบเสร็จรับเงิน หรือ ใบส่งของ' },
                    { key: '', name: 'completion_notes', type: 'TEXT', size: '-', note: 'ผลการตรวจรับและการทดสอบการทำงานหลังซ่อม' },
                    { key: '', name: 'photos', type: 'TEXT', size: '-', note: 'ลิงก์รูปภาพประกอบการซ่อมก่อนและหลัง (JSON Array)' },
                    { key: '', name: 'status', type: 'VARCHAR', size: '50', note: 'สถานะปัจจุบันของใบแจ้งซ่อม (เช่น pending_supervisor, approved, completed)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            },
            {
                num: '3.11',
                title: 'ตารางที่ 3.11 ตาราง Maintenances (ข้อมูลบันทึกประวัติการซ่อมบำรุงพื้นฐาน)',
                tableName: 'maintenances',
                rows: [
                    { key: 'PK', name: 'maintenance_code', type: 'VARCHAR', size: '10', note: 'รหัสบันทึกการซ่อมบำรุง (Primary Key)' },
                    { key: 'FK', name: 'user_id', type: 'VARCHAR', size: '10', note: 'รหัสผู้แจ้งซ่อม (อ้างอิง users.user_id)' },
                    { key: 'FK', name: 'skytrain_code', type: 'VARCHAR', size: '10', note: 'รหัสรถรางไฟฟ้าที่ซ่อม (อ้างอิง electric_trains.skytrain_code)' },
                    { key: '', name: 'repair_details', type: 'VARCHAR', size: '255', note: 'รายละเอียดรายการชำรุดและการซ่อมแซม' },
                    { key: '', name: 'repair_notification_date', type: 'DATE', size: '-', note: 'วันที่แจ้งซ่อม' },
                    { key: '', name: 'repair_start_date', type: 'DATE', size: '-', note: 'วันที่เริ่มดำเนินการซ่อมแซม' },
                    { key: '', name: 'date_of_repair_completion', type: 'DATE', size: '-', note: 'วันที่ซ่อมบำรุงแล้วเสร็จ' },
                    { key: '', name: 'repair_status', type: 'VARCHAR', size: '50', note: 'สถานะการซ่อมบำรุง' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            }
        ]
    },
    {
        moduleTitle: 'หมวดที่ 5: ระบบการแจ้งเตือนและข่าวสารประชาสัมพันธ์ (Notifications & Announcements)',
        tables: [
            {
                num: '3.12',
                title: 'ตารางที่ 3.12 ตาราง Notifications (ข้อมูลการแจ้งเตือนและข่าวสาร)',
                tableName: 'notifications',
                rows: [
                    { key: 'PK', name: 'id', type: 'BIGINT', size: '-', note: 'รหัสการแจ้งเตือน (Primary Key Auto Increment)' },
                    { key: '', name: 'title', type: 'VARCHAR', size: '255', note: 'หัวข้อการแจ้งเตือน หรือ ข่าวสารประชาสัมพันธ์' },
                    { key: '', name: 'content', type: 'TEXT', size: '-', note: 'เนื้อหาข้อความรายละเอียดการแจ้งเตือน' },
                    { key: '', name: 'type', type: 'VARCHAR', size: '50', note: 'ประเภทการแจ้งเตือน (เช่น Announcement, Alert, Service Delay)' },
                    { key: 'FK', name: 'user_id', type: 'VARCHAR', size: '10', note: 'รหัสผู้สร้างประกาศ (อ้างอิง users.user_id)' },
                    { key: '', name: 'created_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่บันทึกข้อมูลเข้าสู่ระบบ' },
                    { key: '', name: 'updated_at', type: 'TIMESTAMP', size: '-', note: 'วันและเวลาที่แก้ไขข้อมูลล่าสุด' }
                ]
            }
        ]
    }
];

// Document structure
const docChildren = [];

// Cover / Header
docChildren.push(
    new Paragraph({
        children: [
            new TextRun({
                text: 'พจนานุกรมข้อมูล (Data Dictionary)',
                bold: true,
                size: 36, // 18pt
                font: fontName
            })
        ],
        alignment: AlignmentType.CENTER,
        spacing: { after: 120 }
    }),
    new Paragraph({
        children: [
            new TextRun({
                text: 'ระบบติดตามรถรางไฟฟ้ามหาวิทยาลัยราชภัฏยะลา (YRU Train Tracking System)',
                bold: true,
                size: 28, // 14pt
                color: '1F2937',
                font: fontName
            })
        ],
        alignment: AlignmentType.CENTER,
        spacing: { after: 260 }
    }),
    new Paragraph({
        children: [
            new TextRun({
                text: 'เอกสารแสดงโครงสร้างฐานข้อมูล (Database Schema) ทั้งหมดของระบบ แบ่งออกเป็น 5 หมวดหมู่หลักตามฟังก์ชันการทำงาน เพื่อใช้ประกอบเอกสารรายงานโครงงาน บทที่ 3 (การวิเคราะห์และออกแบบระบบ)',
                size: 24, // 12pt
                font: fontName
            })
        ],
        spacing: { after: 360 }
    })
);

// Table column widths (Total = 9000 dxa)
const colWidths = [1000, 2200, 1500, 1100, 3200];

const tableBorder = {
    top: { style: BorderStyle.SINGLE, size: 6, color: '6B7280' },
    bottom: { style: BorderStyle.SINGLE, size: 6, color: '6B7280' },
    left: { style: BorderStyle.SINGLE, size: 6, color: '6B7280' },
    right: { style: BorderStyle.SINGLE, size: 6, color: '6B7280' },
    insideHorizontal: { style: BorderStyle.SINGLE, size: 4, color: 'D1D5DB' },
    insideVertical: { style: BorderStyle.SINGLE, size: 4, color: 'D1D5DB' }
};

modulesData.forEach((mod) => {
    // Module Heading
    docChildren.push(
        new Paragraph({
            children: [
                new TextRun({
                    text: mod.moduleTitle,
                    bold: true,
                    size: 30, // 15pt
                    color: '1E3A8A', // Deep Blue
                    font: fontName
                })
            ],
            spacing: { before: 360, after: 180 },
            keepWithNext: true
        })
    );

    mod.tables.forEach((tableInfo) => {
        // Table Title
        docChildren.push(
            new Paragraph({
                children: [
                    new TextRun({
                        text: tableInfo.title,
                        bold: true,
                        size: 26, // 13pt
                        color: '111827',
                        font: fontName
                    })
                ],
                spacing: { before: 200, after: 120 },
                keepWithNext: true
            })
        );

        // Header Row
        const headerRow = new TableRow({
            tableHeader: true,
            children: [
                createCell('คีย์', 0, true, AlignmentType.CENTER, 'E5E7EB'),
                createCell('ชื่อฟิลด์', 1, true, AlignmentType.LEFT, 'E5E7EB'),
                createCell('ชนิดข้อมูล', 2, true, AlignmentType.CENTER, 'E5E7EB'),
                createCell('ขนาด', 3, true, AlignmentType.CENTER, 'E5E7EB'),
                createCell('หมายเหตุ', 4, true, AlignmentType.LEFT, 'E5E7EB')
            ]
        });

        // Data Rows
        const dataRows = tableInfo.rows.map(r => {
            const isPk = r.key === 'PK';
            const isFk = r.key === 'FK';
            const bg = isPk ? 'FEF3C7' : isFk ? 'E0F2FE' : 'FFFFFF'; // Amber for PK, Sky for FK

            return new TableRow({
                children: [
                    createCell(r.key, 0, isPk || isFk, AlignmentType.CENTER, bg),
                    createCell(r.name, 1, isPk, AlignmentType.LEFT, 'FFFFFF'),
                    createCell(r.type, 2, false, AlignmentType.CENTER, 'FFFFFF'),
                    createCell(r.size, 3, false, AlignmentType.CENTER, 'FFFFFF'),
                    createCell(r.note, 4, false, AlignmentType.LEFT, 'FFFFFF')
                ]
            });
        });

        const docxTable = new Table({
            width: { size: 9000, type: WidthType.DXA },
            borders: tableBorder,
            rows: [headerRow, ...dataRows]
        });

        docChildren.push(docxTable);
    });
});

function createCell(text, colIdx, isBold, align, bgColor) {
    return new TableCell({
        width: { size: colWidths[colIdx], type: WidthType.DXA },
        verticalAlign: VerticalAlign.CENTER,
        shading: {
            fill: bgColor,
            type: ShadingType.CLEAR
        },
        margins: {
            top: 100,
            bottom: 100,
            left: 120,
            right: 120
        },
        children: [
            new Paragraph({
                alignment: align,
                children: [
                    new TextRun({
                        text: text,
                        bold: isBold,
                        size: 24, // 12pt
                        font: fontName
                    })
                ]
            })
        ]
    });
}

const doc = new Document({
    styles: {
        default: {
            document: {
                run: {
                    font: fontName,
                    size: 24
                }
            }
        }
    },
    sections: [
        {
            properties: {
                page: {
                    margin: {
                        top: 1440, // 1 inch
                        right: 1440,
                        bottom: 1440,
                        left: 1440
                    }
                }
            },
            children: docChildren
        }
    ]
});

async function main() {
    const buffer = await Packer.toBuffer(doc);
    const rootPath = path.resolve('Data_Dictionary_YRU_Train_Tracking_System.docx');
    fs.writeFileSync(rootPath, buffer);
    console.log(`Successfully generated cleanly organized Word document at: ${rootPath}`);
}

main().catch(console.error);
