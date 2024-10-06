<div class="modal fade" id="modal-add-stock">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Stok Produk</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
    <input type="hidden" name="produk_id" id="produk_id" value="">
    <div class="form-group">
        <label for="jumlah">Jumlah Stok:</label>
        <input type="number" name="jumlah" class="form-control" required>
    </div>
</div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
