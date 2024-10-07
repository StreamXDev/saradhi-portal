@extends('layouts.admin')

@section('content')
<div class="page-title">
    <h1 class="title">Import Members</h1>
    <div>
        <a href="#" class="btn btn-secondary">Import Failed</a>
        <a href="#" class="btn btn-primary">Import New</a>
    </div>
</div>
<div class="page-content">
    <div style="display: flex; align-items:stretch; column-gap:2rem">
        <div style="flex: 1">
            <h4>Success</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="flex: 1">
            <h4>Failed</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Membership ID</th>
                        <th>Member ID</th>
                        <th>Sub ID</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
