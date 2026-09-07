@extends('Admin.Layout.Index')

@section('content')
    <style>
        .container-fluid { padding: 20px 20px 0 20px !important; min-height: 0 !important; }
        .table { margin-bottom: 0 !important; }
    </style>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center"><h4 class="page-title">Category</h4></div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('Admin.Dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Category</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body category-actions">
                        <button
                            type="button"
                            class="btn btn-success"
                            data-toggle="modal"
                            data-target="#addCategoryModal"
                        >
                            Add Category
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($category as $categoryItem)
                                    <tr>
                                        <td>{{ $categoryItem->id }}</td>
                                        <td>{{ $categoryItem->name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCategoryModal{{ $categoryItem->id }}">
                                                Edit
                                            </button>
                                            <form action="{{ route('Admin.Category.Delete', ['category' => $categoryItem->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa category này? Các khóa học thuộc category này sẽ chuyển thành chưa phân loại.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Chưa có category.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-body">
                        {{ $category->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('Admin.Category.Store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="category-name">Name</label>
                            <input type="text" id="category-name" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($category as $categoryItem)
        <div class="modal fade" id="editCategoryModal{{ $categoryItem->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('Admin.Category.Update', ['category' => $categoryItem->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Category</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="category-name-{{ $categoryItem->id }}">Name</label>
                                <input type="text" id="category-name-{{ $categoryItem->id }}" name="name" value="{{ $categoryItem->name }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
