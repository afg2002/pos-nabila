@extends('layouts.app')

@section('title', 'Agenda Barang Masuk')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @livewire('incoming-goods-agenda-management')
        </div>
    </div>
</div>
@endsection