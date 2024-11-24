<?php
use app\models\UserModel;

/** @var $params array */
$userModel = $params['model'];
$roles = $params['roles'];
$userRole = $params['userRole'];
$isAdmin = $params['isAdmin'];
$stockActivities = $params['stockActivities'];
?>

<div class="row">
    <div class="col-12 <?= !empty($stockActivities) ? 'col-lg-8' : 'col-lg-12' ?>">
        <div class="card">
            <form action="/processUpdateUser" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($userModel->id) ?>">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <p class="mb-0">Izmena Korisnika</p>
                        <button class="btn btn-primary btn-sm ms-auto">Sačuvaj</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Ime</label>
                                <input class="form-control" type="text" name="first_name"
                                       value="<?php echo htmlspecialchars($userModel->first_name) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Prezime</label>
                                <input class="form-control" type="text" name="last_name"
                                       value="<?php echo htmlspecialchars($userModel->last_name) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="form-group">
                                <label class="form-control-label">Email</label>
                                <input class="form-control" type="email" name="email"
                                       value="<?php echo htmlspecialchars($userModel->email) ?>" required>
                            </div>
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="col-md-6 mt-3">
                                <div class="form-group">
                                    <label class="form-control-label">Uloga</label>
                                    <select name="role" class="form-control" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['rolaID'] ?>"
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
    </div>

    <?php if (!empty($stockActivities)): ?>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Istorija Promena Zaliha</h6>
                </div>
                <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
                    <div class="timeline timeline-one-side">
                        <?php foreach ($stockActivities as $activity): ?>
                            <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="<?= $activity['tip_promene'] === 'Ulaz' ? 'fas fa-plus text-success' : 'fas fa-minus text-danger' ?>"></i>
                            </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">
                                        <?= htmlspecialchars($activity['proizvod']) ?>
                                    </h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        <?= date('d.m.Y H:i', strtotime($activity['datum_promene'])) ?>
                                    </p>
                                    <p class="text-sm mt-3 mb-2">
                                        <?= $activity['tip_promene'] ?> - Količina: <?= $activity['kolicina'] ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .timeline {
        margin-left: 20px;
    }

    .timeline-block {
        position: relative;
    }

    .timeline-step {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e9ecef;
        position: absolute;
        left: -35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .timeline-content {
        border-left: 2px solid #e9ecef;
        padding-left: 15px;
        margin-bottom: 15px;
    }
</style>