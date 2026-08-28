BOOKING SYSTEM SETUP

The site now has a PHP booking system with:
- date/time selection;
- automatic availability check;
- capacity rules per tour;
- booking reference;
- bookings saved in data/bookings.json;
- email notification to the owner and customer (if PHP mail() is configured);
- private admin panel at /admin.php to mark bookings pending/confirmed/cancelled.

IMPORTANT BEFORE PUBLISHING
1. Open api/config.php.
2. Change ADMIN_PASSWORD from CHANGE-THIS-PASSWORD to a strong password.
3. Confirm OWNER_EMAIL.
4. Upload the whole folder to PHP hosting (PHP 8+ recommended).
5. Make sure the data/ folder is writable by PHP and is protected from direct web access.
6. Test a booking before accepting real customers.

The booking form does not charge the customer automatically. This preserves the existing Revolut/PayPal links and lets you confirm availability first.

If your hosting does not support PHP, this backend will not work from a local file:// URL; it needs a web server with PHP.
