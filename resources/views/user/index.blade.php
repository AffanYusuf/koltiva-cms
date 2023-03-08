@extends('layouts.layout')
  
@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 table-responsive">
        <br />
        <h4>Users Access</h4>
        <br />
        <div align="right">
            <button type="button" name="create_record" id="create_record" class="btn btn-success"> <i class="bi bi-plus-square"></i> Add</button>
        </div>
        <br />
            <table class="table table-striped table-bordered user_datatable"> 
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Photo</th>
                        <th width="180px">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
 
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
        <form method="post" id="sample_form" class="form-horizontal">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel">Add New Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="form_result"></span>
                <div class="form-group">
                    <label>Name : </label>
                    <input type="text" name="name" id="name" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Email : </label>
                    <input type="email" name="email" id="email" class="form-control" />
                </div>
                <div class="form-group editpass">
                    <label>Password : </label>
                    <input type="password" name="password" id="password" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Photo : </label>
                    <input type="file" name="photo" placeholder="Choose image" id="photo">
                </div>
                <img id="preview-image-before-upload" src="" alt="preview image" style="max-height: 250px;">
                <input type="hidden" name="action" id="action" value="Add" />
                <input type="hidden" name="hidden_id" id="hidden_id" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <input type="submit" name="action_button" id="action_button" value="Add" class="btn btn-info" />
            </div>
        </form>  
        </div>
        </div>
    </div>
 
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
        <form method="post" class="form-horizontal">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 align="center" id="confirm-text" style="margin:0;">Are you sure you want to remove this data?</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">OK</button>
            </div>
        </form>  
        </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('.user_datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users.index') }}",
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'photo', name: 'photo',  orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        $('#photo').change(function(){
            let reader = new FileReader();
            reader.onload = (e) => { 
                $('#preview-image-before-upload').attr('src', e.target.result); 
            }
            reader.readAsDataURL(this.files[0]); 
        });
        $('#create_record').click(function(){
            $('.editpass').show();
            $('#sample_form')[0].reset();
            $('#preview-image-before-upload').removeAttr('src');
            $('.modal-title').text('Add New Record');
            $('#action_button').val('Add');
            $('#action').val('Add');
            $('#form_result').html('');
            
            $('#formModal').modal('show');
        });
     
        $('#sample_form').on('submit', function(event){
            event.preventDefault(); 
            var action_url = '';
     
            if($('#action').val() == 'Add')
            {
                action_url = "{{ route('users.store') }}";
            }
     
            if($('#action').val() == 'Edit')
            {
                action_url = "{{ route('users.update') }}";
            }
            var formData = new FormData(this);
            console.log('DATA : ', formData);
            $.ajax({
                type: 'post',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: action_url,
                data: formData,
                dataType: 'json',
                cache:false,
                contentType: false,
                processData: false,
                success: function(data) {
                    console.log('success: '+data);
                    var html = '';
                    if(data.errors)
                    {
                        html = '<div class="alert alert-danger">';
                        for(var count = 0; count < data.errors.length; count++)
                        {
                            html += '<p>' + data.errors[count] + '</p>';
                        }
                        html += '</div>';
                    }
                    if(data.success)
                    {
                        html = '<div class="alert alert-success">' + data.success + '</div>';
                        $('#sample_form')[0].reset();
                        $('#formModal').modal('hide');
                        $('.user_datatable').DataTable().ajax.reload();
                    }
                    $('#form_result').html(html);
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                }
            });
        });
     
        $(document).on('click', '.edit', function(event){
            event.preventDefault(); 
            var id = $(this).attr('id');
            $('#form_result').html('');
     
             
     
            $.ajax({
                url :"/users/edit/"+id+"/",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                dataType:"json",
                success:function(data)
                {
                    console.log('success: '+data);
                    $('#name').val(data.result.name);
                    $('#email').val(data.result.email);
                    $('#hidden_id').val(id);
                    $('.modal-title').text('Edit Record');
                    $('#action_button').val('Update');
                    $('#action').val('Edit'); 
                    $('.editpass').hide(); 
                    $('#formModal').modal('show');
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                }
            })
        });
     
        var user_id;
     
        $(document).on('click', '.delete', function(){
            user_id = $(this).attr('id');
            $('#confirm-text').text('Are you sure you want to remove this data?');
            $('#ok_button').text('OK');
            $('#confirmModal').modal('show');
        });
     
        $('#ok_button').click(function(){
            $.ajax({
                url:"users/destroy/"+user_id,
                beforeSend:function(){
                    $('#ok_button').text('Deleting...');
                },
                success:function(data)
                {
                    $('#confirm-text').text('Data succesfully deleted!');
                    setTimeout(function(){
                    $('#confirmModal').modal('hide');
                    $('.user_datatable').DataTable().ajax.reload();
                    }, 5000);
                }
            })
        });
    });
    </script>
@endsection