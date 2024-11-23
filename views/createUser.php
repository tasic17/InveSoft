<?php
use app\models\UserModel;
/** @var $params UserModel */
?>

<div class="card">
    <form action="/processCreateUser" method="post">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center">
                <p class="mb-0">Create User</p>
                <button class="btn btn-success btn-sm ms-auto" type="submit">Save</button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-uppercase text-sm">User Information</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="first_name" class="form-control-label">First name</label>
                        <input class="form-control" type="text" id="first_name" name="first_name"
                               value="<?php echo $params->first_name ?>" required>
                        <?php
                        if ($params != null && $params->errors != null) {
                            foreach ($params->errors as $attribute => $error) {
                                if ($attribute == 'first_name') {
                                    echo "<span class='text-danger'>$error[0]</span>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="last_name" class="form-control-label">Last name</label>
                        <input class="form-control" type="text" id="last_name" name="last_name"
                               value="<?php echo $params->last_name ?>" required>
                        <?php
                        if ($params != null && $params->errors != null) {
                            foreach ($params->errors as $attribute => $error) {
                                if ($attribute == 'last_name') {
                                    echo "<span class='text-danger'>$error[0]</span>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="email" class="form-control-label">Email address</label>
                        <input class="form-control" type="email" id="email" name="email"
                               value="<?php echo $params->email ?>" required>
                        <?php
                        if ($params != null && $params->errors != null) {
                            foreach ($params->errors as $attribute => $error) {
                                if ($attribute == 'email') {
                                    echo "<span class='text-danger'>$error[0]</span>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-control-label">Password</label>
                        <input class="form-control" type="password" id="password" name="password" required>
                        <?php
                        if ($params != null && $params->errors != null) {
                            foreach ($params->errors as $attribute => $error) {
                                if ($attribute == 'password') {
                                    echo "<span class='text-danger'>$error[0]</span>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirm_password" class="form-control-label">Confirm Password</label>
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" required>
                        <?php
                        if ($params != null && $params->errors != null) {
                            foreach ($params->errors as $attribute => $error) {
                                if ($attribute == 'confirm_password') {
                                    echo "<span class='text-danger'>$error[0]</span>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>