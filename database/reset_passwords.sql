-- =====================================================
-- PASSWORD RESET — run this in phpMyAdmin if login fails
-- Sets all users password to: Admin@1234
-- =====================================================

UPDATE users SET password = '$2y$12$ackJnVcEOYtJZ8gVUVQfleAQLH22xgsNfxwPLoLLOfXub5a0Mhw/m'
WHERE employee_id IN ('EMP001','EMP002','EMP003','EMP004','EMP005');

-- Verify: should show 5 rows
SELECT employee_id, name, email, LEFT(password,20) AS pw_prefix FROM users;
