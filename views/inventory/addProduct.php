<?php
// views/inventory/addProduct.php
?>
<div class="card">
    <form action="/inventory/process-add-product" method="post">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center">
                <p class="mb-0">Dodaj Novi Proizvod</p>
                <button class="btn btn-primary btn-sm ms-auto">Sačuvaj</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Naziv</label>
                        <input type="text" name="naziv" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Kategorija</label>
                        <select name="kategorijaID" class="form-control" required>
                            <?php foreach ($params['kategorije'] as $kategorija): ?>
                                <option value="<?= $kategorija['kategorijaID'] ?>"><?= $kategorija['naziv'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-control-label">Opis</label>
                        <textarea name="opis" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Cena</label>
                        <input type="number" name="cena" class="form-control" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Početna Količina</label>
                        <input type="number" name="pocetna_kolicina" class="form-control" value="0" required>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>