@extends('layouts.app')

@section('title', 'Manajemen Satuan Produk')

@section('content')
<div class="container-fluid">
    @livewire('product-unit-management', [], key('product-unit-management'))
</div>
@endsection