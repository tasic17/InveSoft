<?php
use app\models\UserModel;

/** @var $params array */
$userModel = $params['model'];
$roles = $params['roles'];
$userRole = $params['userRole'];
$isAdmin = $params['isAdmin'];
?>

<div class="card">
    <form action="/processUpdateUser" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($userModel->id ?? '') ?>">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center">
                <p class="mb-0">Izmena Korisnika</p>
                <button class="btn btn-primary btn-sm ms-auto" type="submit">Sačuvaj</button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-uppercase text-sm">Informacije o Korisniku</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Ime</label>
                        <input class="form-control <?php echo isset($userModel->errors['first_name']) ? 'is-invalid' : '' ?>"
                               type="text"
                               name="first_name"
                               value="<?php echo htmlspecialchars($userModel->first_name ?? '') ?>"
                               required>
                        <?php if (isset($userModel->errors['first_name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($userModel->errors['first_name'][0]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Prezime</label>
                        <input class="form-control <?php echo isset($userModel->errors['last_name']) ? 'is-invalid' : '' ?>"
                               type="text"
                               name="last_name"
                               value="<?php echo htmlspecialchars($userModel->last_name ?? '') ?>"
                               required>
                        <?php if (isset($userModel->errors['last_name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($userModel->errors['last_name'][0]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-control-label">Email adresa</label>
                        <input class="form-control <?php echo isset($userModel->errors['email']) ? 'is-invalid' : '' ?>"
                               type="email"
                               name="email"
                               value="<?php echo htmlspecialchars($userModel->email ?? '') ?>"
                               required>
                        <?php if (isset($userModel->errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($userModel->errors['email'][0]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Uloga</label>
                            <select name="role" class="form-control" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo htmlspecialchars($role['rolaID']) ?>"
                                        <?php echo ($userRole && $userRole['rolaID'] == $role['rolaID']) ? 'selected' : '' ?>>
                                        <?php echo htmlspecialchars($role['ime']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>