<?= $this->extend('layouts/analytics') ?>

<?= $this->section('content') ?>

<?php $validation = session('validation') ?? ($validation ?? null); ?>

<div class="container mt-4 mb-5">
    <h2 class="mb-3">My Profile</h2>
    <p class="text-muted">Manage your alumni profile, credentials, and employment history.</p>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Personal Information</h4>
            <form method="post" action="/profile" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="bio">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4"><?= esc(old('bio', $profile['bio'] ?? '')) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="linkedin_url">LinkedIn URL</label>
                    <input class="form-control" type="url" id="linkedin_url" name="linkedin_url" value="<?= esc(old('linkedin_url', $profile['linkedin_url'] ?? '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile_image_url">Profile Image URL</label>
                    <input class="form-control" type="url" id="profile_image_url" name="profile_image_url" value="<?= esc(old('profile_image_url', $profile['profile_image_url'] ?? '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile_image">Upload Profile Image</label>
                    <input class="form-control" type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.gif,.webp">
                </div>
                <?php if (!empty($profile['profile_image_url'])): ?>
                    <p><strong>Current image:</strong> <a href="<?= esc($profile['profile_image_url']) ?>" target="_blank"><?= esc($profile['profile_image_url']) ?></a></p>
                <?php endif; ?>
                <button class="btn btn-primary" type="submit"><?= $profile ? 'Update Profile' : 'Create Profile' ?></button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Degrees</h4>
            <form method="post" action="/profile/degrees" class="mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="">
                <div class="row g-2">
                    <div class="col-md-4"><input class="form-control" type="text" name="degree_name" placeholder="Degree name"></div>
                    <div class="col-md-4"><input class="form-control" type="url" name="institution_url" placeholder="Institution URL"></div>
                    <div class="col-md-3"><input class="form-control" type="date" name="completion_date"></div>
                    <div class="col-md-1"><button class="btn btn-primary w-100" type="submit">Add</button></div>
                </div>
            </form>
            <table class="table table-sm">
                <thead><tr><th>Degree</th><th>Institution URL</th><th>Completion Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($degrees as $item): ?>
                    <tr>
                        <td colspan="4">
                            <form method="post" action="/profile/degrees" class="row g-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="degree_name" value="<?= esc($item['degree_name']) ?>"></div>
                                <div class="col-md-4"><input class="form-control form-control-sm" type="url" name="institution_url" value="<?= esc($item['institution_url']) ?>"></div>
                                <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="completion_date" value="<?= esc($item['completion_date']) ?>"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form method="post" action="/profile/degrees/delete/<?= $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($degrees)): ?><tr><td colspan="4">No degrees added yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Certificates</h4>
            <form method="post" action="/profile/certificates" class="mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="">
                <div class="row g-2">
                    <div class="col-md-4"><input class="form-control" type="text" name="certificate_name" placeholder="Certificate name"></div>
                    <div class="col-md-4"><input class="form-control" type="text" name="issuer_name" placeholder="Issuer name"></div>
                    <div class="col-md-3"><input class="form-control" type="date" name="completion_date"></div>
                    <div class="col-md-1"><button class="btn btn-primary w-100" type="submit">Add</button></div>
                </div>
            </form>
            <table class="table table-sm">
                <thead><tr><th>Certificate</th><th>Issuer</th><th>Completion Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($certificates as $item): ?>
                    <tr>
                        <td colspan="4">
                            <form method="post" action="/profile/certificates" class="row g-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="certificate_name" value="<?= esc($item['certificate_name']) ?>"></div>
                                <div class="col-md-4"><input class="form-control form-control-sm" type="text" name="issuer_name" value="<?= esc($item['issuer_name']) ?>"></div>
                                <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="completion_date" value="<?= esc($item['completion_date']) ?>"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form method="post" action="/profile/certificates/delete/<?= $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($certificates)): ?><tr><td colspan="4">No certificates added yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Licenses</h4>
            <form method="post" action="/profile/licenses" class="mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="">
                <div class="row g-2">
                    <div class="col-md-3"><input class="form-control" type="text" name="license_name" placeholder="License name"></div>
                    <div class="col-md-3"><input class="form-control" type="url" name="license_url" placeholder="License URL"></div>
                    <div class="col-md-2"><input class="form-control" type="date" name="completion_date"></div>
                    <div class="col-md-2"><input class="form-control" type="date" name="expiration_date"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Add</button></div>
                </div>
            </form>
            <table class="table table-sm">
                <thead><tr><th>License</th><th>URL</th><th>Completion</th><th>Expiration</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($licenses as $item): ?>
                    <tr>
                        <td colspan="5">
                            <form method="post" action="/profile/licenses" class="row g-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="license_name" value="<?= esc($item['license_name']) ?>"></div>
                                <div class="col-md-3"><input class="form-control form-control-sm" type="url" name="license_url" value="<?= esc($item['license_url']) ?>"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="completion_date" value="<?= esc($item['completion_date']) ?>"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="expiration_date" value="<?= esc($item['expiration_date']) ?>"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form method="post" action="/profile/licenses/delete/<?= $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($licenses)): ?><tr><td colspan="5">No licenses added yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Professional Courses</h4>
            <form method="post" action="/profile/courses" class="mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="">
                <div class="row g-2">
                    <div class="col-md-4"><input class="form-control" type="text" name="course_name" placeholder="Course name"></div>
                    <div class="col-md-4"><input class="form-control" type="url" name="provider_url" placeholder="Provider URL"></div>
                    <div class="col-md-3"><input class="form-control" type="date" name="completion_date"></div>
                    <div class="col-md-1"><button class="btn btn-primary w-100" type="submit">Add</button></div>
                </div>
            </form>
            <table class="table table-sm">
                <thead><tr><th>Course</th><th>Provider URL</th><th>Completion Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($courses as $item): ?>
                    <tr>
                        <td colspan="4">
                            <form method="post" action="/profile/courses" class="row g-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="course_name" value="<?= esc($item['course_name']) ?>"></div>
                                <div class="col-md-4"><input class="form-control form-control-sm" type="url" name="provider_url" value="<?= esc($item['provider_url']) ?>"></div>
                                <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="completion_date" value="<?= esc($item['completion_date']) ?>"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form method="post" action="/profile/courses/delete/<?= $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?><tr><td colspan="4">No professional courses added yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Employment History</h4>
            <form method="post" action="/profile/employment" class="mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="">
                <div class="row g-2">
                    <div class="col-md-3"><input class="form-control" type="text" name="company_name" placeholder="Company name"></div>
                    <div class="col-md-3"><input class="form-control" type="text" name="job_title" placeholder="Job title"></div>
                    <div class="col-md-2"><input class="form-control" type="date" name="start_date"></div>
                    <div class="col-md-2"><input class="form-control" type="date" name="end_date"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Add</button></div>
                </div>
            </form>
            <table class="table table-sm">
                <thead><tr><th>Company</th><th>Job Title</th><th>Start Date</th><th>End Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($employmentHistory as $item): ?>
                    <tr>
                        <td colspan="5">
                            <form method="post" action="/profile/employment" class="row g-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="company_name" value="<?= esc($item['company_name']) ?>"></div>
                                <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="job_title" value="<?= esc($item['job_title']) ?>"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="start_date" value="<?= esc($item['start_date']) ?>"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="end_date" value="<?= esc($item['end_date']) ?>"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Save</button></div>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form method="post" action="/profile/employment/delete/<?= $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($employmentHistory)): ?><tr><td colspan="5">No employment history added yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
