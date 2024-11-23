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
                        <select name="tip_promene" class="form-control" required id="tipPromene">
                            <option value="Ulaz">Ulaz</option>
                            <option value="Izlaz">Izlaz</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Količina</label>
                        <input type="number"
                               name="kolicina"
                               class="form-control"
                               min="0"
                               oninput="this.value = this.value < 0 ? 0 : Math.floor(this.value)"
                               required>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const kolicinaInput = form.querySelector('input[name="kolicina"]');
        const tipPromeneSelect = document.getElementById('tipPromene');

        // Add form submission handler
        form.addEventListener('submit', function(e) {
            const kolicina = parseInt(kolicinaInput.value);
            if (kolicina < 0) {
                e.preventDefault();
                alert('Količina ne može biti negativna!');
                return false;
            }
        });

        // Add input validation
        kolicinaInput.addEventListener('input', function() {
            this.value = this.value < 0 ? 0 : Math.floor(this.value);
        });
    });
</script>