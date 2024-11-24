<div class="card">
    <div class="card-header pb-0">
        <div class="d-flex align-items-center">
            <h6>Users</h6>
            <a class="btn btn-primary btn-sm ms-auto" href="/createUser">Create</a>
        </div>
    </div>
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                    <th class="text-secondary opacity-7">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($params as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm"><?= $user['first_name'] ?> <?= $user['last_name'] ?></h6>
                                    <p class="text-xs text-secondary mb-0"><?= $user['email'] ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs font-weight-bold mb-0"><?= $user['role'] ?></p>
                        </td>
                        <td class="align-middle">
                            <?php if ($user['role'] !== 'Administrator'): ?>
                                <a href="/updateUser?id=<?= $user['id'] ?>" class="text-secondary font-weight-bold text-xs me-3">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="/deleteUser?id=<?= $user['id'] ?>"
                                   class="text-danger font-weight-bold text-xs"
                                   onclick="return confirm('Da li ste sigurni da želite da obrišete ovog korisnika?');">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>