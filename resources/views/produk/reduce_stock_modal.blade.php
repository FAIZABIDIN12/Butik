<div class="modal fade" id="modal-reduce-stock" tabindex="-1" role="dialog" aria-labelledby="modal-reduce-stockLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-reduce-stockLabel">Kurangi Stok Produk</h4>
            </div>
            <div class="modal-body">
                <form method="post" id="form-reduce-stock">
                    @csrf
                    @method('put') <!-- Pastikan metode ini sesuai dengan metode yang digunakan di backend -->
                    <div class="form-group">
                        <label for="jumlah" class="control-label">Jumlah</label>
                        <input type="number" class="form-control" name="jumlah" id="jumlah" min="1" required>
                        <input type="hidden" id="produk_id" name="produk_id">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger" form="form-reduce-stock">Kurangi Stok</button>
            </div>
        </div>
    </div>
</div>
