<div class="card">
    <div class="card-header pb-0">
        <div class="d-flex align-items-center">
            <h6>Korisnici</h6>
            <a class="btn btn-primary btn-sm ms-auto" href="/createUser">Dodaj Novog</a>
        </div>
    </div>
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Korisnik</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Uloga</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aktivnost na Zalihama</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Poslednja Aktivnost</th>
                    <th class="text-secondary opacity-7">Akcije</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($params as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h6>
                                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($user['role']) ?></p>
                        </td>
                        <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold">
                                <?= $user['stock_changes'] ?> promena
                            </span>
                        </td>
                        <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold">
                                <?= $user['last_activity'] ? date('d.m.Y H:i', strtotime($user['last_activity'])) : 'Nema aktivnosti' ?>
                            </span>
                        </td>
                        <td class="align-middle">
                            <?php if ($user['role'] !== 'Administrator' || (isset($isAdmin) && $isAdmin)): ?>
                                <a href="/updateUser?id=<?= $user['id'] ?>" class="btn btn-link text-primary px-3 mb-0">
                                    <i class="fas fa-edit me-2"></i>Izmeni
                                </a>
                                <?php if ($user['role'] !== 'Administrator'): ?>
                                    <a href="/deleteUser?id=<?= $user['id'] ?>"
                                       class="btn btn-link text-danger px-3 mb-0"
                                       onclick="return confirm('Da li ste sigurni da želite da obrišete ovog korisnika?');">
                                        <i class="fas fa-trash me-2"></i>Obriši
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>