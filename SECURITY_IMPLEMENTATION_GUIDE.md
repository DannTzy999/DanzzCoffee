# SECURITY IMPLEMENTATION GUIDE - Laracoffee

Panduan praktis untuk mengimplementasikan rekomendasi security di project Laracoffee.

---

## 🚀 QUICK START - Implementasi Prioritas Tertinggi

### 1. FIX XSS VULNERABILITY (Critical - 5 minutes)

**File:** `app/helpers.php`

**Current Code (VULNERABLE):**
```php
function myFlasherBuilder($message, $success = false, $failed = false)
{
    if ($success == true) {
        $status = "success";
        $logo = "check-circle-fill";
    } else if ($failed == true) {
        $status = "danger";
        $logo = "exclamation-triangle-fill";
    }

    Session::flash('message', '<div class="alert alert-' . $status . '...
        <div>' . $message . '</div>  // ❌ VULNERABLE TO XSS
    ...
}
```

**FIXED CODE:**
```php
use Illuminate\Support\Facades\Session;

function myFlasherBuilder($message, $success = false, $failed = false)
{
    // Sanitize message to prevent XSS
    $message = htmlspecialchars(trim($message), ENT_QUOTES, 'UTF-8');
    
    $status = $success ? "success" : ($failed ? "danger" : "warning");
    $logo = $success ? "check-circle-fill" : ($failed ? "exclamation-triangle-fill" : "info-circle-fill");

    Session::flash('message', '<div class="alert alert-' . $status . ' d-flex justify-content-between align-items-center mt-3" role="alert">
        <i class="bi bi-' . $logo . ' me-2" style="font-size:1.5rem"></i>
        <div>' . $message . '</div>
        <button type="button" class="btn-close ms-auto p-2 bd-highlight" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>');
}
```

**Implementasi:** Copy paste kode di atas dan ganti file `helpers.php`.

---

### 2. IMPLEMENT ACTIVITY LOGGING (Critical - 30 minutes)

**Step 1: Create ActivityLog Model & Migration**

```bash
php artisan make:model ActivityLog -m
```

**Step 2: Edit Migration File** (`database/migrations/[timestamp]_create_activity_logs_table.php`)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // 'login', 'logout', 'approve_order', etc
            $table->string('resource_type')->nullable(); // 'Order', 'Product', 'User'
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('details')->nullable(); // Additional data
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
```

**Step 3: Update ActivityLog Model** (`app/Models/ActivityLog.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'details',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

**Step 4: Create Helper Function** (Add to `app/helpers.php`)

```php
use App\Models\ActivityLog;

function logActivity($action, $resourceType = null, $resourceId = null, $details = null)
{
    try {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    } catch (\Exception $e) {
        // Silently fail - don't break functionality if logging fails
        \Log::error('Failed to log activity', ['error' => $e->getMessage()]);
    }
}
```

**Step 5: Add Logging to AuthController** (`app/Http/Controllers/AuthController.php`)

```php
public function loginPost(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email:dns',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        
        // Log successful login
        logActivity('login', 'User', auth()->id());
        
        $message = "Login success";
        myFlasherBuilder(message: $message, success: true);
        return redirect('/home');
    }

    // Log failed login attempt
    logActivity('login_failed', 'User', null, [
        'email' => $credentials['email'],
        'reason' => 'invalid_credentials'
    ]);

    $message = "Wrong credential";
    myFlasherBuilder(message: $message, failed: true);
    return back();
}

public function logoutPost()
{
    try {
        // Log logout before destroying session
        logActivity('logout', 'User', auth()->id());
        
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $message = "Session ended, you logout <strong>successfully</strong>";

        myFlasherBuilder(message: $message, success: true);

        return redirect('/auth');
    } catch (\Illuminate\Database\QueryException $exception) {
        return abort(500);
    }
}
```

**Step 6: Add Logging to OrderController** (Example: `approveOrder` method)

```php
public function approveOrder(Order $order, Product $product)
{
    // ... existing validation code ...

    if ($order->isDirty()) {
        $oldStatus = $order->getOriginal('status_id');
        $order->save();

        // Log admin action
        logActivity('approve_order', 'Order', $order->id, [
            'product_id' => $product->id,
            'status_changed_from' => $oldStatus,
            'status_changed_to' => $order->status_id,
            'quantity' => $order->quantity,
        ]);

        // ... rest of code ...
    }
}
```

**Step 7: Run Migration**

```bash
php artisan migrate
```

---

### 3. ADD RATE LIMITING (High - 10 minutes)

**File:** `routes/web.php`

**Current Code:**
```php
Route::post('/auth/login', [AuthController::class, "loginPost"]);
```

**FIXED CODE:**
```php
// Login dengan rate limiting
Route::post('/auth/login', [AuthController::class, "loginPost"])
    ->middleware('throttle:5,1'); // Max 5 attempts per 1 minute

// Register dengan rate limiting
Route::post('/auth/register', [AuthController::class, "registrationPost"])
    ->middleware('throttle:10,60'); // Max 10 registrations per 60 minutes

// File uploads dengan rate limiting
Route::post("/profile/edit_profile/{user:id}", [ProfileController::class, "editProfilePost"])
    ->middleware('throttle:20,60');

Route::post("/product/add_product", [ProductController::class, "addProductPost"])
    ->middleware('throttle:30,60');
```

**Cara Mengimplementasikan:**
1. Buka `routes/web.php`
2. Cari `Route::post('/auth/login'...` dan `Route::post('/auth/register'...`
3. Tambahkan `->middleware('throttle:X,Y')` di akhir route

---

### 4. STRENGTHEN PASSWORD POLICY (High - 15 minutes)

**File:** `app/Http/Controllers/AuthController.php`

**Current Code (WEAK):**
```php
'password' => 'required|confirmed|min:4',
```

**FIXED CODE:**
```php
// Di registrationPost method
$validatedData = $request->validate([
    'fullname' => 'required|max:255',
    'username' => 'required|max:15',
    'email' => 'required|email',
    'password' => [
        'required',
        'confirmed',
        'min:8',
        'regex:/[a-z]/', // at least one lowercase letter
        'regex:/[A-Z]/', // at least one uppercase letter
        'regex:/[0-9]/', // at least one digit
    ],
    'phone' => 'required|numeric',
    'gender' => 'required',
    'address' => 'required',
]);
```

**Custom Error Messages (Optional but Better UX):**
```php
$validatedData = $request->validate([
    // ... other fields ...
    'password' => [
        'required',
        'confirmed',
        'min:8',
        'regex:/[a-z]/',
        'regex:/[A-Z]/',
        'regex:/[0-9]/',
    ]
], [
    'password.min' => 'Password harus minimal 8 karakter',
    'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
    'password.confirmed' => 'Password confirmation tidak cocok'
]);
```

**Update di changePasswordPost juga:**
```php
$validated = $request->validate([
    "current_password" => "required|min:8",
    "password" => [
        "required",
        "confirmed",
        "min:8",
        "regex:/[a-z]/",
        "regex:/[A-Z]/",
        "regex:/[0-9]/",
    ],
    "password_confirmation" => "required|min:8",
]);
```

---

### 5. FILE UPLOAD SECURITY (High - 20 minutes)

**File:** `app/Http/Controllers/ProductController.php`

**Current Code (VULNERABLE):**
```php
$validatedData = $request->validate([
    // ...
    "image" => "image|max:2048"
]);
```

**FIXED CODE:**
```php
$validatedData = $request->validate([
    "product_name" => "required|max:25",
    "stock" => "required|numeric|gt:0",
    "price" => "required|numeric|gt:0",
    "discount" => "required|numeric|gt:0|lt:100",
    "orientation" => "required",
    "description" => "required|max:1000",
    "image" => [
        "nullable",
        "image",
        "mimetypes:image/jpeg,image/png,image/gif,image/webp",
        "max:2048",
        "dimensions:min_width=100,min_height=100"
    ]
]);

// Validate and store file
if ($request->hasFile("image")) {
    $file = $request->file("image");
    
    // Verify file is valid
    if (!$file->isValid()) {
        return back()->withErrors(['image' => 'File upload failed']);
    }
    
    // Additional MIME type check (magic bytes)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file->getRealPath());
    finfo_close($finfo);
    
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
        return back()->withErrors(['image' => 'Invalid file type']);
    }
    
    // Generate secure filename
    $filename = time() . '_' . hash('sha256', $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
    
    // Store file
    $validatedData["image"] = $file->storeAs('products', $filename, 'public');
} else {
    $validatedData["image"] = env("IMAGE_PRODUCT");
}
```

**Apply Same Fix to:**
- `ProfileController::editProfilePost()`
- `OrderController::makeOrderPost()` (proof_payment)

**Create `.htaccess` file** di `storage/app/products/` dan `storage/app/profile/`:

```apache
# storage/app/products/.htaccess
<FilesMatch "\.(php|phtml|php3|php4|php5|php6|php7|php8|pht|phar|phps|shtml)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

### 6. ADD MISSING POLICY CHECKS (Medium - 15 minutes)

**File:** `app/Policies/OrderPolicy.php`

**Add new method:**
```php
public function filterByStatus(User $user, $status_id)
{
    // Only admins can filter all orders
    // Regular users can only see their own (via orderDataFilter business logic)
    return $user->role_id == Role::ADMIN_ID;
}
```

**File:** `routes/web.php`

**Add policy check to route:**
```php
// Before (VULNERABLE)
Route::get("/order/order_data/{status_id}", "orderDataFilter");

// After (PROTECTED)
Route::get("/order/order_data/{status_id}", "orderDataFilter")
    ->middleware('can:filterByStatus,App\Models\Order');
```

---

### 7. CUSTOM ERROR HANDLING (Medium - 30 minutes)

**File:** `app/Exceptions/Handler.php`

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions with context
            Log::error('Exception occurred', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'url' => request()->url(),
                'method' => request()->method(),
                'ip' => request()->ip(),
            ]);
        });
    }

    public function render($request, Throwable $exception)
    {
        // Handle HTTP exceptions
        if ($this->isHttpException($exception)) {
            return $this->renderHttpException($exception);
        }

        // Production: render generic error
        if (app()->environment('production')) {
            if ($exception->getCode() >= 500) {
                return response()->view('errors.500', [], 500);
            }
            if ($exception->getCode() >= 400 && $exception->getCode() < 500) {
                return response()->view('errors.400', [], $exception->getCode());
            }
        }

        return parent::render($request, $exception);
    }
}
```

**Create Error View Files:**

**File:** `resources/views/errors/500.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">⚠️ Server Error</h4>
        <p>Oops! Something went wrong on our end.</p>
        <hr>
        <p class="mb-0">
            <small>Our team has been notified about this issue. Please try again later.</small>
        </p>
    </div>
    <a href="/" class="btn btn-primary">Back to Home</a>
</div>
@endsection
```

**File:** `resources/views/errors/400.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">⚠️ Error {{ $exception->getStatusCode() }}</h4>
        <p>{{ $exception->getMessage() ?: 'The requested resource could not be found.' }}</p>
    </div>
    <a href="/" class="btn btn-primary">Back to Home</a>
</div>
@endsection
```

---

## 📝 IMPLEMENTATION CHECKLIST

### Phase 1: CRITICAL (Complete Within 1 Week)

- [ ] Fix XSS vulnerability in helpers.php
- [ ] Implement activity logging (ActivityLog model + migrations)
- [ ] Add logging to AuthController (login/logout)
- [ ] Add logging to OrderController (admin actions)
- [ ] Add rate limiting to auth routes
- [ ] Strengthen password policy (min 8 chars + complexity)

### Phase 2: HIGH PRIORITY (Complete Within 2 Weeks)

- [ ] Implement file upload MIME type whitelist
- [ ] Add magic bytes verification to file uploads
- [ ] Create .htaccess files to prevent script execution
- [ ] Add missing policy checks to routes
- [ ] Implement custom error handling
- [ ] Create error view templates (500, 400)

### Phase 3: MEDIUM PRIORITY (Complete Within 1 Month)

- [ ] Implement 2FA (TOTP)
- [ ] Add admin dashboard for activity logs
- [ ] Setup Sentry for error monitoring
- [ ] Implement HTTPS + security headers
- [ ] Setup automated dependency scanning
- [ ] Create security audit trail UI

### Phase 4: NICE-TO-HAVE (Long-term)

- [ ] Implement API rate limiting separately
- [ ] Add IP whitelist/blacklist functionality
- [ ] Implement DLP (Data Loss Prevention) for exports
- [ ] Add session timeout warnings
- [ ] Implement two-person rule for critical operations

---

## 🔍 VERIFICATION STEPS

After implementing each security fix:

### 1. Test XSS Fix
```
Try to login with: admin@test.com" onload="alert('XSS')"
Expected: Alert should NOT appear, message should be escaped
```

### 2. Test Rate Limiting
```
Try to login 6 times in 1 minute
Expected: On 6th attempt, get "429 Too Many Requests" response
```

### 3. Test Activity Logging
```
Login as admin
Expected: ActivityLog entry created with action='login'

Approve an order
Expected: ActivityLog entry created with action='approve_order'
```

### 4. Test Password Policy
```
Try to register with password "1234"
Expected: Validation error "Password harus minimal 8 karakter"

Try with "Test1234"
Expected: Registration successful
```

### 5. Test File Upload Security
```
Try to upload .php file as profile image
Expected: File rejected with error message

Upload valid .png file
Expected: File accepted and stored
```

---

## 📚 RESOURCES & TOOLS

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Fortify - 2FA](https://laravel.com/docs/fortify)
- [Snyk - Dependency Scanning](https://snyk.io/)
- [Sentry - Error Monitoring](https://sentry.io/)

---

## ⚠️ IMPORTANT NOTES

1. **Always test in development first** before deploying to production
2. **Run tests** after each security change: `php artisan test`
3. **Backup database** before running migrations
4. **Review code** before deploying to production
5. **Monitor logs** after deployment to catch any issues

---

**Last Updated:** 2026-06-16  
**Priority Status:** URGENT - Critical vulnerabilities need immediate attention
