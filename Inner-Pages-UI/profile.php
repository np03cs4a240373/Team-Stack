<div class="page-header">
    <div class="container">
        <h1>My Profile</h1>
        <p>Update your personal information</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:640px; margin:0 auto;">

        <!-- This shows success message after profile update -->
        <?php if ($success): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- This shows error message if something goes wrong -->
        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- This card shows user profile information -->
        <div class="card mb-3" style="text-align:center; padding:2rem;">

            <!-- If user has profile picture, show image -->
            <?php if ($existingPic): ?>
                <?php $picMtime = @filemtime($uploadBase . '/' . $existingPic) ?: time(); ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($existingPic) ?>?v=<?= $picMtime ?>" alt="Profile"
                     style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 1rem;border:3px solid #e0f7fa;display:block;">

            <!-- If no profile picture, show first letter of name -->
            <?php else: ?>
                <div style="width:80px;height:80px;border-radius:50%;background:#00b4d8;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;margin:0 auto 1rem;">
                    <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>

            <!-- User name and email -->
            <h2 style="font-size:1.2rem; font-weight:700;"><?= htmlspecialchars($user['name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>

            <!-- User role badge -->
            <span class="status-badge <?= $user['role']==='employer'?'status-accepted':($user['role']==='admin'?'status-reviewed':'status-pending') ?>" style="margin-top:0.5rem; display:inline-block;">
                <?= ucfirst($user['role']) ?>
            </span>
        </div>

        <!-- This card contains edit profile form -->
        <div class="card">
            <h3 style="font-size:1rem; font-weight:600; margin-bottom:1.5rem;">Edit Information</h3>

            <!-- Form sends updated data to profile page -->
            <form method="POST" action="<?= BASE_URL ?>/pages/profile.php" data-validate enctype="multipart/form-data">
                <?= csrfField() ?>

                <!-- Upload profile picture -->
                <div class="form-group">
                    <label class="form-label" for="profile_pic">Profile Picture</label>
                    <input type="file" id="profile_pic" name="profile_pic"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           class="form-control"
                           style="padding:0.45rem 1rem;">
                    <small class="text-muted">JPG, PNG, GIF or WebP · Max 3 MB</small>
                </div>

                <!-- User full name input -->
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                           class="form-control"
                           value="<?= htmlspecialchars($user['name']) ?>"
                           required>
                    <span class="form-error">Name is required.</span>
                </div>

                <!-- Email is disabled because user cannot edit it -->
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled
                           style="opacity:0.6; cursor:not-allowed;">
                    <small class="text-muted">Email cannot be changed.</small>
                </div>

                <div class="grid-2">

                    <!-- Phone number input -->
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               class="form-control"
                               placeholder="98XXXXXXXX"
                               maxlength="10"
                               pattern="[0-9]{10}"
                               inputmode="numeric"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        <span class="form-error">Phone number must be exactly 10 digits.</span>
                    </div>

                    <!-- Location input -->
                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location"
                               class="form-control"
                               placeholder="e.g. Kathmandu"
                               value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                    </div>
                </div>

                <!-- Bio textarea -->
                <div class="form-group">
                    <label class="form-label" for="bio">Bio / About</label>
                    <textarea id="bio" name="bio"
                              class="form-control"
                              rows="3"
                              style="resize:none;"
                              placeholder="Tell employers a bit about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>

                <!-- Password change section -->
                <hr style="border:none; border-top:1px solid var(--border); margin:1.5rem 0;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:1rem; color:var(--text-muted);">
                    Change Password (leave blank to keep current)
                </h4>

                <div class="grid-2">

                    <!-- New password input -->
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" id="password" name="password"
                               class="form-control"
                               placeholder="Min. 8 chars, upper, lower, number, special">
                    </div>

                    <!-- Confirm password input -->
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control"
                               placeholder="Repeat new password">
                    </div>
                </div>

                <!-- Submit button -->
                <button type="submit" class="btn btn-primary">Save Changes</button>

            </form>
        </div>

    </div>
</div>