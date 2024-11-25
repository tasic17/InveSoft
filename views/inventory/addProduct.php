<?php
// views/inventory/addProduct.php
?>
<div class="card">
    <form action="/inventory/process-add-product" method="post">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center">
                <p class="mb-0">Dodaj Novi Proizvod</p>
                <button type="submit" class="btn btn-primary btn-sm ms-auto">Sačuvaj</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Naziv <span class="text-danger">*</span></label>
                        <input type="text" name="naziv" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Kategorija <span class="text-danger">*</span></label>
                        <select name="kategorijaID" class="form-control" required>
                            <option value="">Izaberite kategoriju</option>
                            <?php foreach ($params['kategorije'] as $kategorija): ?>
                                <option value="<?= $kategorija['kategorijaID'] ?>"><?= htmlspecialchars($kategorija['naziv']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="form-group">
                        <label class="form-control-label">Opis <span class="text-danger">*</span></label>
                        <textarea name="opis" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="form-group">
                        <label class="form-control-label">Cena <span class="text-danger">*</span></label>
                        <input type="number"
                               name="cena"
                               class="form-control"
                               step="0.01"
                               min="0"
                               required>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="form-group">
                        <label class="form-control-label">Početna Količina</label>
                        <input type="number"
                               name="pocetna_kolicina"
                               class="form-control"
                               value="0"
                               min="0"
                               oninput="this.value = this.value < 0 ? 0 : Math.floor(this.value)">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const cena = parseFloat(this.querySelector('input[name="cena"]').value);
        const kolicina = parseInt(this.querySelector('input[name="pocetna_kolicina"]').value);

        if (cena < 0) {
            e.preventDefault();
            alert('Cena ne može biti negativna!');
            return;
        }

        if (kolicina < 0) {
            e.preventDefault();
            alert('Količina ne može biti negativna!');
            return;
        }
    });
</script>