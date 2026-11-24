MEDIQQO AI - LOCAL ACADEMIC DEMONSTRATION

REQUIREMENTS
- XAMPP with Apache, MySQL/MariaDB and PHP
- A browser
- The mediqqo_ai database and the eight tables created in phpMyAdmin

INSTALLATION
1. Extract this folder into C:\xampp\htdocs\
2. Confirm the folder is named Mediqqo_AI_Complete.
3. Start Apache and MySQL in XAMPP.
4. Open config/database.php. The default local XAMPP settings are:
   host=localhost, database=mediqqo_ai, username=root, password empty.
   If your MySQL password is different, update it there.
5. Open http://localhost/Mediqqo_AI_Complete/setup.php ONCE.
6. If setup succeeds, DELETE setup.php from the project folder.
7. Open http://localhost/Mediqqo_AI_Complete/

DEMO STAFF LOGINS
Doctor: doctor@mediqqo.local
Password: DoctorDemo123!

Nurse: nurse@mediqqo.local
Password: NurseDemo123!

PATIENT DEMO FLOW
1. Sign in as doctor or nurse.
2. Open Patients > Invite patient.
3. Enter a fictional email address.
4. Copy the invitation link shown.
5. Open that link in the browser and register a fictional patient.
6. Sign in as the patient using the email and password you created.

IMPORTANT
- Use fictional data only.
- This is an academic local demo, not a production clinical system.
- The invitation link is displayed on screen; no email is sent automatically.
- The educational insight generator uses simple rule-based summaries, not a trained AI model.
- It does not diagnose, prescribe, or replace qualified clinical judgement.
- Before any real deployment, obtain qualified security, privacy, legal and clinical review.
- Do not expose this local demonstration to the public internet.
