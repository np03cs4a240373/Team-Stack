<div class="page-header">
    <div class="container">
        <h1>Manage Users</h1>
        <p>View and delete user accounts</p>
    </div>
</div>

<div class="container section">

    <!-- filter by role -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>
            <a href="/admin/users.php" class="btn btn-primary btn-sm">All</a>
            <a href="/admin/users.php?role=seeker" class="btn btn-outline btn-sm">Job Seekers</a>
            <a href="/admin/users.php?role=employer" class="btn btn-outline btn-sm">Employers</a>
            <a href="/admin/users.php?role=admin" class="btn btn-outline btn-sm">Admins</a>
            <span class="text-muted text-sm" style="margin-left:auto;">3 user(s)</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- shown when no users found -->
                <!-- <tr>
                    <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">No users found.</td>
                </tr> -->

                <!-- example row: current logged in admin user, no delete button -->
                <tr>
                    <td class="text-muted">1</td>
                    <td><strong>Admin User</strong></td>
                    <td>admin@example.com</td>
                    <td>
                        <span class="status-badge status-reviewed">
                            Admin
                        </span>
                    </td>
                    <td>Kathmandu</td>
                    <td>Jan 01, 2025</td>
                    <td>
                        <span class="text-muted text-sm">You</span>
                    </td>
                </tr>

                <!-- example row: employer user with delete button -->
                <tr>
                    <td class="text-muted">2</td>
                    <td><strong>Ram Sharma</strong></td>
                    <td>ram@company.com</td>
                    <td>
                        <span class="status-badge status-accepted">
                            Employer
                        </span>
                    </td>
                    <td>Pokhara</td>
                    <td>Feb 10, 2025</td>
                    <td>
                        <a href="/api/delete.php?type=user&id=2"
                            onclick="return confirm('Delete user Ram Sharma? This will also delete their jobs and applications.')"
                            class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>

                <!-- example row: seeker user with delete button -->
                <tr>
                    <td class="text-muted">3</td>
                    <td><strong>Sita Thapa</strong></td>
                    <td>sita@gmail.com</td>
                    <td>
                        <span class="status-badge status-pending">
                            Seeker
                        </span>
                    </td>
                    <td>—</td>
                    <td>Mar 05, 2025</td>
                    <td>
                        <a href="/api/delete.php?type=user&id=3"
                            onclick="return confirm('Delete user Sita Thapa? This will also delete their jobs and applications.')"
                            class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>