# Local Fixes Applied

The project was statically audited and the PHP files pass syntax validation.

## Important fixes

1. Login no longer builds the entire dashboard inside the login request.
   A dashboard/database query failure can no longer make a valid password appear to be a failed login.

2. Successful logins update `users.last_login_at` and `users.last_login_ip`.

3. Local email verification is disabled by default.
   Set `EMAIL_VERIFICATION=true` only after configuring a working mail service.

4. Signup treats email delivery as optional during local development instead of turning a successful account creation into a server failure.

5. Frontend API handling now reports non-JSON PHP responses instead of failing silently.

6. Dashboard loading now displays an error in the UI when an API/database request fails.

7. Added `diagnose.php` for local database/table diagnostics.

## Run

```powershell
php -S localhost:8000
```

Then open:

- http://localhost:8000/diagnose.php
- http://localhost:8000/register.html
- http://localhost:8000/login.html

If `diagnose.php` reports missing tables, import `database_schema.sql` into `elearning_db`.
