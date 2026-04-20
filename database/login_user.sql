SELECT id, name, email, role, status, created_at
FROM users
ORDER BY id DESC;


SELECT id, tokenable_id, name, last_used_at, created_at
FROM personal_access_tokens
ORDER BY id DESC;




SELECT id, user_id, store_name, is_approved
FROM vendors
ORDER BY id ASC;


SELECT id, user_id, store_name, is_approved
FROM vendors
ORDER BY id ASC;