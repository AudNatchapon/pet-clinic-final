# รายงานการวิเคราะห์ความต่างของระบบ (DRIFT_REPORT.md)

**ระบบงาน:** PetCare Portal (HTS #2B - การปรับปรุงระบบงาน 2 ส่วนงาน)  
**ขอบเขตการตรวจ:** เปรียบเทียบโค้ดเดิมใน `DATABASE_FINAL_LASTED_NOT_EDIT_EVER` กับโค้ดที่ปรับปรุงแล้วใน `HTS2B_VERSIONS/v05_final_integrated`  
**เอกสารอ้างอิง:** `design.md`, `HEURISTIC_RULES.md`, `DB_NOTES.md`, `VALIDATION_PLAN.md`, และ `AI_WORKFLOW.md`

---

## 1. คำนิยามประเภทของ Drift (A, B, C)

ตามข้อกำหนดใน `AI_WORKFLOW.md` ขั้นที่ 6 (Drift Audit) ความแตกต่างระหว่างโค้ดเดิมกับข้อกำหนดในระบบถูกจำแนกออกเป็น 3 ประเภท โดยมีการตัดสินใจโดยมนุษย์ (Human Decision) ดังนี้:

* **ประเภท A (Token Mismatch):**  
  ความต่างที่มี Token กำหนดไว้ใน `design.md` อยู่แล้ว แต่โค้ดเดิมใช้ค่า Hardcoded (เช่น สี hex, rem, px ที่ไม่ตรงกับ 8px Grid)  
  ➡️ **การตัดสินใจ:** **แก้โค้ด (Fix Code)** ให้เรียกใช้ CSS Variables จาก `css/petcare-tokens.css` ทั้งหมด
* **ประเภท B (Design System Gap & UX Extension):**  
  ความต่างที่ `design.md` มีหลักการกว้างๆ (เช่น Voice & Tone, Heuristic Rules) แต่ไม่ได้ประกาศ Component Markup หรือ Token ไว้โดยตรง  
  ➡️ **การตัดสินใจ:** **แก้โค้ด / เพิ่ม Component / ยอมรับตามเดิม** โดยนำหลักการ Heuristic มาออกแบบ Markup เพิ่มเติม และควบคุมให้ใช้เฉพาะสี/สเปซซิ่งที่มาจาก Semantic Tokens ที่มีอยู่ ห้ามแก้ `design.md` เอง
* **ประเภท C (Database Constraint & Business Logic Preservation):**  
  ความต่างหรือข้อจำกัดที่เกิดจากสเปกของฐานข้อมูลจริง (`DB_NOTES.md` / `pet_hospital (18).sql`) หรือ Business Logic เดิม  
  ➡️ **การตัดสินใจ:** **คงสเปกฐานข้อมูลและ Logic เดิมไว้ 100%** โดยสะท้อนข้อจำกัดขึ้นมาเป็นส่วนหนึ่งของการทำงานบน UI (เช่น Input Validation, Read-only fields, CASCADE warnings)

---

## 2. ตาราง Drift Audit รวมทั้ง 2 ส่วนงาน (Dashboard & Pet List)

| ลำดับ | จุดที่ต่าง | ไฟล์และตำแหน่ง | ค่าใน design.md / DB_NOTES | ค่าที่พบในโค้ดเดิม | ประเภท | การตัดสินใจ | เหตุผลและการดำเนินการ |
|:---:|:---|:---|:---|:---|:---:|:---:|:---|
| 1 | **ขนาดตัวเลขสรุป KPI** | `index.php` (KPI Card) | `--font-size-2xl: 32px` | `2.2rem` (~35.2px) | **A** | **แก้โค้ด** | ปรับให้ใช้ `var(--font-size-2xl)` ตามข้อกำหนด R-01 ให้ตัวเลขสถิติเด่นชัดตามระบบ Typography |
| 2 | **Padding ภายในการ์ด KPI** | `index.php` (KPI Card) | `--space-md: 16px` | `20px` (ไม่ลง 8px grid) | **A** | **แก้โค้ด** | ปรับเป็น `padding: var(--space-md)` (16px) เพื่อให้เป็นไปตามระบบ 8px Grid System |
| 3 | **ระยะห่างตาราง Grid บน Dashboard** | `index.php` (Layout) | `--space-lg: 24px` | Bootstrap `g-3` (16px) | **A** | **แก้โค้ด** | ปรับเป็น `gap: var(--space-lg)` (24px) ตามเกณฑ์ R-01 เพื่อแก้ปัญหาความแออัดของหน้าจอ |
| 4 | **โครงสร้างเมนูหลัก (App Shell vs Header)** | `index.php` (Menu) | เลย์เอาต์เมนูด้านข้าง Sidebar 250px ตาม Prototype | การ์ดเมนูขนาดใหญ่ 4 การ์ดกองอยู่ด้านบน | **B** | **แก้โค้ด** | ปรับเปลี่ยนเป็น Sidebar ด้านข้าง (`<aside class="sidebar">`) ตามต้นแบบ `index.html` เพื่อลด Visual Clutter |
| 5 | **การแยกสถิติโรคและภาระงานแพทย์** | `index.php` (ส่วนล่าง) | R-01: แยกสถิติลงใน Dropdown เฉพาะ | แสดงตารางโรคและภาระงานแพทย์คู่กันจนล้นจอ | **B** | **แก้โค้ด** | เพิ่มฟังก์ชัน Dropdown "เลือกข้อมูลที่ต้องการดู" พร้อมระบบค้นหาชื่อแพทย์แบบ Real-time |
| 6 | **การแสดงผลกราฟวงกลม (Doughnut)** | `index.php` (Chart) | แสดง Top 5 + ส่วนที่เกินเป็น "อื่น ๆ" | ดึงข้อมูลทั้งหมดมาพล็อตจนเสี้ยววงกลมแน่น | **B** | **แก้โค้ด** | จัดกลุ่มข้อมูล Top 5 แยกสีตาม Design Token และรวมส่วนที่เกินเป็น "อื่น ๆ" (`#D9D9D9`) |
| 7 | **ตัวเลขผลรวมตรงกลางวงกลม** | `index.php` (Chart Plugin) | จัดวางกึ่งกลางรูโดนัท ไม่ล้นหลุดเฟรม | Position absolute ทับซ้อนจนตัวเลขล้น | **B** | **แก้โค้ด** | พัฒนา `centerTextPlugin` วาดบน Canvas คำนวณจาก `chartArea` พร้อมระบบแสดงตัวเลขตามหัวข้อที่เลือก |
| 8 | **สถานะ Error เมื่อ DB ขัดข้อง** | `index.php` (Header) | H9: Voice & Tone บอกสาเหตุและทางออก | พังเป็นข้อผิดพลาดดิบของ PHP/PDO | **B** | **แก้โค้ด** | ครอบ `try-catch` และแสดง Custom Error Banner ภาษาไทย พร้อมปุ่มจำลอง Error State สำหรับตรวจงาน |
| 9 | **สีพื้นหลังหน้า Pet List** | `pet_list.php:34` | `--color-background: #9CD3EF` | `#f8f9fa` (สีเทา Bootstrap) | **A** | **แก้โค้ด** | ปรับให้ใช้ `var(--color-background)` จาก Design Tokens |
| 10 | **รัศมีความโค้งมุม Card สัตว์เลี้ยง** | `pet_list.php:36` | `--radius-md: 8px` | `20px` (โค้งมนเกินสเปก) | **A** | **แก้โค้ด** | ปรับให้ใช้ `var(--radius-md)` (8px) ให้สอดคล้องกับโทเค็นความโค้งมนของระบบ |
| 11 | **การกรองประเภทสัตว์เลี้ยง** | `pet_list.php` (Filter) | R-04 / H5: Category Filter Chips | ไม่มีตัวกรองประเภทสัตว์เลี้ยง | **B** | **แก้โค้ด** | เพิ่ม Filter Chips (ทั้งหมด, สุนัข, แมว, อื่น ๆ) ให้สามารถกรองข้อมูลได้ในคลิกเดียว |
| 12 | **รูปภาพสัตว์เลี้ยง (Thumbnail)** | `pet_list.php` (List Card) | R-04: Thumbnail Component 48x48px | ไม่มีรูปภาพ มีเฉพาะข้อความชื่อ | **B** | **แก้โค้ด** | เพิ่ม Thumbnail Component ขนาด 48x48px (`--thumbnail-size: 48px`, `--thumbnail-radius: 8px`) |
| 13 | **ปุ่มลิงก์ไปยังประวัติการรักษา** | `pet_list.php` (Actions) | R-04: ลิงก์ตรงไปหน้าประวัติการรักษา | ต้องกดกลับไปหน้าหลักเพื่อเปิดประวัติ | **B** | **แก้โค้ด** | เพิ่ม History Pill Link (`treatment_history.php?pet_id=...`) ให้นำทางได้โดยตรง |
| 14 | **ข้อความเมื่อค้นหาสัตว์เลี้ยงไม่พบ** | `pet_list.php:78` | H9: อธิบายสาเหตุพร้อมปุ่มล้างคำค้นหา | ข้อความสั้น "ไม่พบข้อมูล" | **B** | **แก้โค้ด** | ออกแบบ Empty State Card ภาษาไทย พร้อมคำแนะนำและปุ่มล้างคำค้นหา |
| 15 | **ข้อจำกัดความยาวชื่อสัตว์เลี้ยง** | `pet_list.php` (Modal) | `pet.name varchar(20)` ตาม DB_NOTES | ไม่จำกัดความยาวในฟอร์มแก้ไข | **C** | **แก้โค้ดบน UI / คง DB เดิม** | เพิ่ม Attribute `maxlength="20"`, ตัวนับอักษร Real-time และข้อความเตือนหากเกิน 20 ตัวอักษร |
| 16 | **รหัสสัตว์เลี้ยงในระบบ (Primary Key)** | `pet_list.php` (Modal) | `pet.pet_ID` (PK, AUTO_INCREMENT) | ช่อง input ธรรมดาที่อาจแก้ไขได้ | **C** | **แก้โค้ดบน UI / คง DB เดิม** | กำหนดช่องรหัสเป็น `readonly` และระบุชัดเจนว่าเป็น "รหัสระบบ (ห้ามแก้ไข)" |
| 17 | **การลบสัตว์เลี้ยง (ON DELETE CASCADE)** | `pet_list.php` (Delete) | `appointments.pet_ID` CASCADE delete | ใช้ `confirm()` เบราว์เซอร์ ไม่เตือนผลกระทบ | **C** | **แก้โค้ดบน UI / คง DB เดิม** | พัฒนา Confirmation Modal เตือนภาษาไทยว่าประวัตินัดหมายและการรักษาทั้งหมดจะถูกลบไปด้วย |
| 18 | **คำสั่ง SQL และ Business Logic** | ทั้งสองหน้าจอ | ห้ามเปลี่ยน SQL, ตัวแปร, API endpoints | ใช้คำสั่งเดิมทั้งหมด | **C** | **คงเดิม 100%** | คำสั่ง Query ข้อมูล, Date Filtering, การคำนวณ KPI, และ API payload คงเดิม 100% |

---

## 3. สรุปผลการ Audit และข้อเสนอแนะ

1. **การควบคุมตามกฎ Heuristics และ Design System:**
   - รายการที่เป็นความต่างประเภท **A (ทั้งหมด 4 รายการ)** ได้รับการแก้ไขให้เชื่อมโยงกับ Token Variables อย่างครบถ้วน ไม่มีการใช้ค่าสีหรือ Spacing นอก `design.md`
   - รายการที่เป็นความต่างประเภท **B (ทั้งหมด 8 รายการ)** ได้รับการพัฒนาเพิ่มเติมตามเกณฑ์ R-01 และ R-04 โดยทุก Component ใหม่ (Sidebar, Dropdown, Doughnut Chart, Empty State, Thumbnail) ถูกสร้างขึ้นโดยใช้เฉพาะ Semantic Tokens เดิมที่มีอยู่ ไม่มีการแก้ไขไฟล์ `design.md`
   - รายการที่เป็นความต่างประเภท **C (ทั้งหมด 4 รายการ)** ได้รับการสะท้อนข้อจำกัดของฐานข้อมูลขึ้นมาเป็นกลไกการตรวจสอบบนหน้า UI เพื่อป้องกันข้อผิดพลาดของผู้ใช้ (H5 Error Prevention) โดยคง Database Schema และ SQL Logic เดิมไว้ 100%

2. **การรับรองคุณภาพ:**
   - โค้ดทั้งหมดผ่านการตรวจรับตาม `VALIDATION_PLAN.md` ครบถ้วนทั้ง 8 ข้อ
   - สามารถนำชุดโค้ดใน `HTS2B_VERSIONS/v05_final_integrated` ไปทดสอบและส่งมอบงานได้อย่างสมบูรณ์แบบ
