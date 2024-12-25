@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Committees</h1>
    </div>
    <div class="actions">
        <a href="/admin/committee/create" class="btn btn-primary">Add Committee</a>
    </div>
</div>
<div class="page-content">
    <div class="list-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Unit/Group</th>
                    <th>Year</th>
                    <th>Formed On</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($committees as $committee)    
                    <tr>
                        <td>{{ $committee->committee_type->name }}</td>
                        <td>{{ $committee->unit ? $committee->unit->name : '--' }}</td>
                        <td>{{ $committee->year }}</td>
                        <td>{{ $committee->formed_on }}</td>
                        <td>
                            <div class="actions">
                                <a href="/admin/committee/show/{{ $committee->id }}/{{$committees->currentPage()}}" class="btn"><i class="fa-solid fa-eye"></i></a>
                                <a href="/admin/committee/edit/{{$committee->id}}" class="btn"><i class="fa-solid fa-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div>{{ $committees->links() }}</div>
    </div>
</div>


@endsection