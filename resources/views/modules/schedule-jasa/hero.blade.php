{{-- Banner sambutan: hanya tampil di tab Dashboard (disembunyikan JS di tab lain). --}}
<section class="sj-hero" data-sj-hero style="--sj-hero-image: url('{{ asset('assets/images/illustrations/bg-login.jpg') }}')" @if (request()->query('tab', 'dashboard') !== 'dashboard') hidden @endif>
    <small>SELAMAT DATANG</small>
    <h1>{{ auth()->user()?->name ?? 'Admin Overhaul' }}</h1>
    <p>Monitoring Rencana Kerja Jasa - Overhaul PT Semen Tonasa</p>
</section>
