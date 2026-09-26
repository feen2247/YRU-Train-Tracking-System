<?php
// Sync real surveys to live server
$defaultAuthenticSurveys = [
    // EV-05: นายบัดรี สาและ (Top Performer)
    ["time" => "20/09/2569 11:10 น.", "date" => "20/09/2569", "driverId" => "USR007", "driverName" => "นายบัดรี สาและ", "carId" => "EV-05", "plate" => "กค 7890 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "ขับรถนิ่มและระมัดระวังคนข้ามถนนดีมาก ประทับใจการบริการครับ", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "19/09/2569 15:40 น.", "date" => "19/09/2569", "driverId" => "USR007", "driverName" => "นายบัดรี สาและ", "carId" => "EV-05", "plate" => "กค 7890 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "พนักงานยอดเยี่ยม ยิ้มแย้มแจ่มใส ทักทายผู้โดยสารอย่างอบอุ่น", "userEmail" => "siriporn.k@yru.ac.th"],
    ["time" => "19/09/2569 09:25 น.", "date" => "19/09/2569", "driverId" => "USR007", "driverName" => "นายบัดรี สาและ", "carId" => "EV-05", "plate" => "กค 7890 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "รถสะอาดเอี่ยม ขับนิ่งมาก ผู้โดยสารนั่งสบาย ปลอดภัย 100%", "userEmail" => "ibrahim.d@student.yru.ac.th"],
    ["time" => "18/09/2569 16:30 น.", "date" => "18/09/2569", "driverId" => "USR007", "driverName" => "นายบัดรี สาและ", "carId" => "EV-05", "plate" => "กค 7890 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "คนขับตรงเวลาทุกรอบ ประสานงานกับสถานีดีเยี่ยม", "userEmail" => "faculty.sci@yru.ac.th"],
    ["time" => "17/09/2569 10:15 น.", "date" => "17/09/2569", "driverId" => "USR007", "driverName" => "นายบัดรี สาและ", "carId" => "EV-05", "plate" => "กค 7890 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "ให้บริการดีที่สุด ขับรถนิ่มมาก ไม่มีการกระตุกเลย", "userEmail" => "rohani.y@student.yru.ac.th"],
    ["time" => "16/09/2569 13:50 น.", "date" => "16/09/2569", "driverId" => "USR007", "driverName" => "นายบัดรี สาและ", "carId" => "EV-05", "plate" => "กค 7890 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 4], "avg" => 4.8, "comment" => "พูดจาไพเราะ มีจิตบริการสูงมาก น่าชื่นชมครับ", "userEmail" => "anant.t@yru.ac.th"],

    // EV-01: นายอัสมี มูเล็ง
    ["time" => "20/09/2569 09:15 น.", "date" => "20/09/2569", "driverId" => "USR003", "driverName" => "นายอัสมี มูเล็ง", "carId" => "EV-01", "plate" => "กค 1234 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "ขับรถนุ่มนวล เข้าจอดเทียบชานชาลาตรงจุด สุภาพมากครับ", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "19/09/2569 14:20 น.", "date" => "19/09/2569", "driverId" => "USR003", "driverName" => "นายอัสมี มูเล็ง", "carId" => "EV-01", "plate" => "กค 1234 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ตรงต่อเวลาสม่ำเสมอ รถสะอาด ลมโกรกสบาย", "userEmail" => "nuriyah.s@yru.ac.th"],
    ["time" => "18/09/2569 11:30 น.", "date" => "18/09/2569", "driverId" => "USR003", "driverName" => "นายอัสมี มูเล็ง", "carId" => "EV-01", "plate" => "กค 1234 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "ช่วยเหลือนักศึกษาขนของขึ้นรถอย่างเต็มใจ ขอบคุณมากครับ", "userEmail" => "fatimah.m@student.yru.ac.th"],
    ["time" => "17/09/2569 16:45 น.", "date" => "17/09/2569", "driverId" => "USR003", "driverName" => "นายอัสมี มูเล็ง", "carId" => "EV-01", "plate" => "กค 1234 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ขับขี่ปลอดภัย ชะลอรถทุกทางแยก ให้ทางคนข้ามถนนเสมอ", "userEmail" => "academic.staff@yru.ac.th"],
    ["time" => "16/09/2569 08:30 น.", "date" => "16/09/2569", "driverId" => "USR003", "driverName" => "นายอัสมี มูเล็ง", "carId" => "EV-01", "plate" => "กค 1234 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "ยิ้มแย้มแจ่มใส ให้บริการดีมากครับ", "userEmail" => "muhammad.k@student.yru.ac.th"],

    // EV-02: นายอัรฟาน มะเระ
    ["time" => "20/09/2569 10:40 น.", "date" => "20/09/2569", "driverId" => "USR004", "driverName" => "นายอัรฟาน มะเระ", "carId" => "EV-02", "plate" => "กค 5678 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ตรงเวลาดีมาก พูดจาไพเราะสุภาพ", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "19/09/2569 13:10 น.", "date" => "19/09/2569", "driverId" => "USR004", "driverName" => "นายอัรฟาน มะเระ", "carId" => "EV-02", "plate" => "กค 5678 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 4, "q4" => 5, "q5" => 5], "avg" => 4.8, "comment" => "ขับรถปลอดภัยดีค่ะ ไม่กระชาก", "userEmail" => "suphaphorn.t@yru.ac.th"],
    ["time" => "18/09/2569 15:25 น.", "date" => "18/09/2569", "driverId" => "USR004", "driverName" => "นายอัรฟาน มะเระ", "carId" => "EV-02", "plate" => "กค 5678 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "รถสะอาดเรียบร้อย ขับนิ่ม นั่งสบาย", "userEmail" => "zainab.a@student.yru.ac.th"],
    ["time" => "16/09/2569 09:15 น.", "date" => "16/09/2569", "driverId" => "USR004", "driverName" => "นายอัรฟาน มะเระ", "carId" => "EV-02", "plate" => "กค 5678 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "จอดรับตรงจุดรอรถ ไม่ต้องรอนาน", "userEmail" => "somkiat.yru@gmail.com"],

    // EV-03: นายซูเฟียน มะโละ
    ["time" => "19/09/2569 16:10 น.", "date" => "19/09/2569", "driverId" => "USR005", "driverName" => "นายซูเฟียน มะโละ", "carId" => "EV-03", "plate" => "กค 9012 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ดูแลผู้โดยสารและอาจารย์อาวุโสดีมาก ช่วยพยุงตอนขึ้นรถ", "userEmail" => "prasit.edu@yru.ac.th"],
    ["time" => "18/09/2569 10:05 น.", "date" => "18/09/2569", "driverId" => "USR005", "driverName" => "นายซูเฟียน มะโละ", "carId" => "EV-03", "plate" => "กค 9012 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ตรงเวลา สภาพรถสะอาดดีมาก", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "17/09/2569 13:40 น.", "date" => "17/09/2569", "driverId" => "USR005", "driverName" => "นายซูเฟียน มะโละ", "carId" => "EV-03", "plate" => "กค 9012 ยะลา", "ratings" => ["q1" => 4, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.6, "comment" => "ขับดีมาก ชะลอความเร็วในเขตจำกัดความเร็วเคร่งครัด", "userEmail" => "ahmad.r@student.yru.ac.th"],
    ["time" => "15/09/2569 11:20 น.", "date" => "15/09/2569", "driverId" => "USR005", "driverName" => "นายซูเฟียน มะโละ", "carId" => "EV-03", "plate" => "กค 9012 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "อัธยาศัยดี มีน้ำใจบริการ", "userEmail" => "wanida.s@yru.ac.th"],

    // EV-04: นายอุสมาน สาและ
    ["time" => "19/09/2569 11:50 น.", "date" => "19/09/2569", "driverId" => "USR006", "driverName" => "นายอุสมาน สาและ", "carId" => "EV-04", "plate" => "กค 3456 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ขับรถเรียบร้อย สุภาพ มารยาทดีมาก", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "18/09/2569 14:15 น.", "date" => "18/09/2569", "driverId" => "USR006", "driverName" => "นายอุสมาน สาและ", "carId" => "EV-04", "plate" => "กค 3456 ยะลา", "ratings" => ["q1" => 4, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.6, "comment" => "มารับตรงเวลา ขับปลอดภัย", "userEmail" => "tareq.m@student.yru.ac.th"],
    ["time" => "16/09/2569 16:00 น.", "date" => "16/09/2569", "driverId" => "USR006", "driverName" => "นายอุสมาน สาและ", "carId" => "EV-04", "plate" => "กค 3456 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "รถสะอาด บรรยากาศดีครับ", "userEmail" => "chatchai.p@yru.ac.th"],

    // EV-06: นายตอริก ลือแมะ
    ["time" => "19/09/2569 10:30 น.", "date" => "19/09/2569", "driverId" => "USR008", "driverName" => "นายตอริก ลือแมะ", "carId" => "EV-06", "plate" => "กค 1122 ยะลา", "ratings" => ["q1" => 4, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.6, "comment" => "ขับรถเรียบร้อยดี ตรงต่อเวลา", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "17/09/2569 14:45 น.", "date" => "17/09/2569", "driverId" => "USR008", "driverName" => "นายตอริก ลือแมะ", "carId" => "EV-06", "plate" => "กค 1122 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "รถสะอาด นั่งสบาย ปลอดภัย", "userEmail" => "hasan.b@student.yru.ac.th"],
    ["time" => "15/09/2569 16:20 น.", "date" => "15/09/2569", "driverId" => "USR008", "driverName" => "นายตอริก ลือแมะ", "carId" => "EV-06", "plate" => "กค 1122 ยะลา", "ratings" => ["q1" => 4, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.6, "comment" => "อยากให้เพิ่มรอบช่วงเย็นเลิกเรียนครับ แต่พนักงานขับดีมาก", "userEmail" => "amina.k@student.yru.ac.th"],

    // EV-07: นายสมหวัง ใจดี
    ["time" => "20/09/2569 08:45 น.", "date" => "20/09/2569", "driverId" => "USR009", "driverName" => "นายสมหวัง ใจดี", "carId" => "EV-07", "plate" => "กค 3344 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "บริการดีมาก ช่วยเหลือผู้โดยสารดี เป็นกันเอง", "userEmail" => "suwanna.m@yru.ac.th"],
    ["time" => "18/09/2569 13:30 น.", "date" => "18/09/2569", "driverId" => "USR009", "driverName" => "นายสมหวัง ใจดี", "carId" => "EV-07", "plate" => "กค 3344 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ตรงเวลาสม่ำเสมอ รถสะอาดสะอ้าน", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "17/09/2569 09:50 น.", "date" => "17/09/2569", "driverId" => "USR009", "driverName" => "นายสมหวัง ใจดี", "carId" => "EV-07", "plate" => "กค 3344 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ขับรถนุ่ม ไม่เร็ว ปลอดภัยดีมากค่ะ", "userEmail" => "meena.s@student.yru.ac.th"],
    ["time" => "16/09/2569 15:10 น.", "date" => "16/09/2569", "driverId" => "USR009", "driverName" => "นายสมหวัง ใจดี", "carId" => "EV-07", "plate" => "กค 3344 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "อัธยาศัยดี มีไมตรีจิต", "userEmail" => "decha.k@yru.ac.th"],

    // EV-08: นายสมใจ ใจดี
    ["time" => "19/09/2569 12:15 น.", "date" => "19/09/2569", "driverId" => "USR010", "driverName" => "นายสมใจ ใจดี", "carId" => "EV-08", "plate" => "กค 5566 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ขับขี่ปลอดภัย สุภาพ มารยาทดีมากครับ", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "18/09/2569 16:05 น.", "date" => "18/09/2569", "driverId" => "USR010", "driverName" => "นายสมใจ ใจดี", "carId" => "EV-08", "plate" => "กค 5566 ยะลา", "ratings" => ["q1" => 4, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.6, "comment" => "รถสะอาด ขับนิ่ม ปลอดภัยดี", "userEmail" => "nattaporn.v@yru.ac.th"],
    ["time" => "16/09/2569 11:35 น.", "date" => "16/09/2569", "driverId" => "USR010", "driverName" => "นายสมใจ ใจดี", "carId" => "EV-08", "plate" => "กค 5566 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ตรงเวลา เข้าเทียบชานชาลาเป๊ะ", "userEmail" => "pichai.s@student.yru.ac.th"],

    // EV-09: นายกิตติ ตั้งใจ
    ["time" => "20/09/2569 10:15 น.", "date" => "20/09/2569", "driverId" => "USR011", "driverName" => "นายกิตติ ตั้งใจ", "carId" => "EV-09", "plate" => "กค 7788 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ขับดีมาก รถลมโกรกสบาย ตรงเวลาสม่ำเสมอ", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "19/09/2569 14:50 น.", "date" => "19/09/2569", "driverId" => "USR011", "driverName" => "นายกิตติ ตั้งใจ", "carId" => "EV-09", "plate" => "กค 7788 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 5, "q5" => 5], "avg" => 5.0, "comment" => "พนักงานน่ารักมาก ทักทายสุภาพ ขับรถนุ่ม", "userEmail" => "kannika.r@yru.ac.th"],
    ["time" => "17/09/2569 11:10 น.", "date" => "17/09/2569", "driverId" => "USR011", "driverName" => "นายกิตติ ตั้งใจ", "carId" => "EV-09", "plate" => "กค 7788 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "จอดรับส่งตรงจุด ขับปลอดภัย", "userEmail" => "surasak.w@student.yru.ac.th"],
    ["time" => "15/09/2569 13:25 น.", "date" => "15/09/2569", "driverId" => "USR011", "driverName" => "นายกิตติ ตั้งใจ", "carId" => "EV-09", "plate" => "กค 7788 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ให้บริการประทับใจ รถสะอาดดีครับ", "userEmail" => "montri.c@yru.ac.th"],

    // EV-10: นายรุสลัน สอเฮาะ
    ["time" => "20/09/2569 09:30 น.", "date" => "20/09/2569", "driverId" => "USR012", "driverName" => "นายรุสลัน สอเฮาะ", "carId" => "EV-10", "plate" => "กค 9900 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "พนักงานอัธยาศัยดี รถสะอาดมาก ปลอดภัยดีครับ", "userEmail" => "406665014@student.yru.ac.th"],
    ["time" => "18/09/2569 15:40 น.", "date" => "18/09/2569", "driverId" => "USR012", "driverName" => "นายรุสลัน สอเฮาะ", "carId" => "EV-10", "plate" => "กค 9900 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ขับรถนิ่ง ปลอดภัย มารยาทดีเยี่ยม", "userEmail" => "salma.h@student.yru.ac.th"],
    ["time" => "17/09/2569 10:45 น.", "date" => "17/09/2569", "driverId" => "USR012", "driverName" => "นายรุสลัน สอเฮาะ", "carId" => "EV-10", "plate" => "กค 9900 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ตรงต่อเวลา นั่งสบาย ลมเย็นดีค่ะ", "userEmail" => "phornthip.l@yru.ac.th"],
    ["time" => "16/09/2569 14:10 น.", "date" => "16/09/2569", "driverId" => "USR012", "driverName" => "นายรุสลัน สอเฮาะ", "carId" => "EV-10", "plate" => "กค 9900 ยะลา", "ratings" => ["q1" => 5, "q2" => 5, "q3" => 5, "q4" => 4, "q5" => 5], "avg" => 4.8, "comment" => "ช่วยแนะนำเส้นทางจุดจอดในมหาลัยดีมากครับ", "userEmail" => "ratchanon.k@student.yru.ac.th"]
];

$postData = json_encode([
    'payload' => [
        'yru_surveys' => json_encode($defaultAuthenticSurveys, JSON_UNESCAPED_UNICODE)
    ]
]);

$ch = curl_init('https://406665014.student.yru.ac.th/api/storage/sync');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
