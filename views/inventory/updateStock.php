<?php
// views/inventory/updateStock.php
?>
<div class="card">
    <form action="/inventory/process-update-stock" method="post">
        <input type="hidden" name="proizvodID" value="<?= $params->proizvodID ?>">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center">
                <p class="mb-0">Ažuriranje Zaliha - <?= $params->naziv ?></p>
                <button class="btn btn-primary btn-sm ms-auto">Sačuvaj</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Tip Promene</label>
                        <select name="tip_promene" class="form-control" required>
                            <option value="Ulaz">Ulaz</option>
                            <option value="Izlaz">Izlaz</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Količina</label>
                        <input type="number" name="kolicina" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>