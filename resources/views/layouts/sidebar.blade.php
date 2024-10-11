<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <!-- <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ url(auth()->user()->foto ?? '') }}" class="img-circle img-profil" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div> -->
        
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MENU</li>
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard blue-icon"></i> <span class="menu-text">Dashboard</span>
                </a>
            </li>

            @if (auth()->user()->level == 1)
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-cubes blue-icon"></i> <span class="menu-text">Master</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu" style="padding-left: 25px;">
                    <li><a href="{{ route('kategori.index') }}"> <span >Kategori</span></a></li>
                    <li><a href="{{ route('produk.index') }}"> <span >Produk</span></a></li>
                    <li><a href="{{ route('member.index') }}"> <span >Member</span></a></li>
                    <li><a href="{{ route('supplier.index') }}"> <span >Supplier</span></a></li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-cart-arrow-down blue-icon"></i> <span class="menu-text">Transaction</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu" style="padding-left: 25px;">
    <li><a href="{{ route('pembelian.index') }}"> <span>Pembelian</span></a></li>
    <li><a href="{{ route('transaksi.baru') }}"> <span>Penjualan</span></a></li>
    <li><a href="{{ route('penjualan.index') }}"> <span>History Penjualan </span></a></li>
</ul>

            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-briefcase blue-icon"></i> <span class="menu-text">Accounting</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu" style="padding-left: 25px;">
                    <li><a href="{{ route('accounts.index') }}"> <span>Akun</span></a></li>
                    <li><a href="{{ route('categories.index') }}"> <span>Jenis Transaksi</span></a></li>
                    <li><a href="{{ route('transaction.index') }}"> <span>Cashflow</span></a></li>
                    <li><a href="{{ route('report.profit_loss') }}"> <span>Laba Rugi</span></a></li>
                    <li><a href="{{ route('report.balance_sheet') }}"> <span>Neraca</span></a></li>
                </ul>
            </li>

            <li class="header">SYSTEM</li>
            <li>
                <a href="{{ route('user.index') }}">
                    <i class="fa fa-users blue-icon"></i> <span class="menu-text">User</span>
                </a>
            </li>
            <li>
                <a href="{{ route("setting.index") }}">
                    <i class="fa fa-cogs blue-icon"></i> <span class="menu-text">Pengaturan</span>
                </a>
            </li>
            @else
            <li>
                <a href="{{ route('transaksi.index') }}">
                    <i class="fa fa-cart-arrow-down blue-icon"></i> <span class="menu-text">Transaksi Aktif</span>
                </a>
            </li>
            <li>
                <a href="{{ route('transaksi.baru') }}">
                    <i class="fa fa-cart-arrow-down blue-icon"></i> <span class="menu-text">Transaksi Baru</span>
                </a>
            </li>
            @endif
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

<!-- Add custom CSS for blue icons and larger, bold menu text -->
<style>
    .blue-icon {
        color: blue !important; /* Change to your desired blue color */
    }
    .menu-text {
        font-weight: bold; /* Make the menu text bold */
        font-size: 16px; /* Increase the font size */
    }
</style>

<!-- Add jQuery script to handle dropdown functionality -->
<script>
$(document).ready(function() {
    $('.treeview a').on('click', function() {
        $(this).parent().siblings().removeClass('active');
        $(this).parent().toggleClass('active');
        $(this).siblings('.treeview-menu').slideToggle();
        $(this).parent().siblings().find('.treeview-menu').slideUp();
    });
});
</script>
