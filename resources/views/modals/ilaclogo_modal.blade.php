@extends('layouts.master')
@section('title') Company Logo Settings @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Company Logo Settings @endslot
@endcomponent
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Company Logo Settings</h4>
            </div>

            <div class="col-xl-12">
                <div class="card-body">
                    <table class="table table-bordered align-middle table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Company Name</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        @foreach($companies as $company)
                            <tbody>
                                <tr>
                                    <td>{{ $company->company_name }}</td>
                                    <td>
                                        <form action="{{ route('companies.updateLogo', $company->id) }}" method="post">
                                            @csrf
                                            <input class="form-check-input" type="checkbox" id="ilca_logo" name="ilca_logo" value="Y" {{ $company->ilca_logo == 'Y' ? 'checked' : '' }}>
                                            <button type="submit" class="btn btn-primary" id="submitbtn">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-modal')
    <script>
        var sessionSuccess = @json(session('success'));
        if (sessionSuccess) {
            toastSuccess(sessionSuccess);
        }
    </script>
@endpush