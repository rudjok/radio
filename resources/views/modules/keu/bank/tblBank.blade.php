@extends('templates.layouts.ng-gentalella')


@section('content')
<div class="container" ng-controller="bankController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" ng-show="!formTampil">
                <div class="panel-heading"> &nbsp</div>

                <div class="panel-body">
                    <div>
                        <h2></h2>
                            <!-- Table-to-load-the-data Part -->
                        <div >
                            <div class="row">
                                <div class="col-lg-12 margin-tb">
                                    <div class="pull-left">
                                        <h2>Master {{ !empty($judul) ? $judul : null }}</h2>
                                    </div>
                                    <div class="pull-right" style="padding-top:30px">
                                        <div class="box-tools" style="display:inline-table">
                                          <div class="input-group">
                                              <input type="text" class="form-control input-sm ng-valid ng-dirty" placeholder="Search" ng-change="searchDB()" ng-model="searchText" name="table_search" title="" tooltip="" data-original-title="Min character length is 3">
                                              <span class="input-group-addon">Search</span>
                                          </div>
                                        </div>
                                        <!-- <button class="btn btn-success" data-toggle="modal" data-target="#create-user">Create New</button> -->
                                    </div>
                                </div>
                            </div>
                            <table class="table table-bordered pagin-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>COA Id</th>
                                        <th>Nama</th>
                                        <th>No. Acc</th>
                                        <th>Alamat</th>
                                        <th>Kota</th>
                                        <th><button id="btn-add" class="btn btn-primary btn-xs" ng-click="toggle('add', 0)">{{ !empty($judul) ? $judul : null }} Baru</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- <tr  dir-paginate="values in customers | itemsPerPage:5" total-items="totalItems"> -->
                                    <tr  dir-paginate="values in _data | itemsPerPage:5" total-items="totalItems">
                                        <td>[[ values.no ]]</td>
                                        <td>[[ values.coa_id_bank ]]</td>
                                        <td>[[ values.nama_bank ]]</td>
                                        <td>[[ values.no_acc ]]</td>
                                        <td>[[ values.alamat ]]</td>
                                        <td>[[ values.kota ]]</td>
                                        <td>
                                            <button class="btn btn-default btn-xs btn-detail" ng-click="toggle('edit', values.no)">Edit</button>
                                            <!--
                                            <button class="btn btn-danger btn-xs btn-delete" ng-click="confirmDelete(values, $index)">Delete</button>
                                            -->
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <p class="pull-left pagination">Menampilan <strong>[[ table.CurrentItems ]]</strong> Dari <strong>[[ table.TotalItems ]]</strong></p>
                                            <dir-pagination-controls class="pull-right" on-page-change="pageChanged(newPageNumber)" template-url="/ext/ng-html/dirPagination.html" ></dir-pagination-controls>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div form-bank class="col-md-12"></div>
        <!--
        <div class="col-md-12" ng-controller="lovCustomerController">
            <div lov-customer></div>
        </div>
        <lov-modal></lov-modal>
        -->
    </div>
</div>
<!-- AngularJS Application Scripts -->

<script src="<?= asset('app/lib/angular-1.5.5/angular-sanitize.min.js') ?>"></script>
<script src="<?= asset('app/lib/uploader/ng-file-upload-bower/ng-file-upload-shim.min.js') ?>"></script>
<script src="<?= asset('app/lib/uploader/ng-file-upload-bower/ng-file-upload.min.js') ?>"></script>
<script src="<?= asset('assets/ng/controllers/keu/bank.min.js') ?>"></script>
<script src="<?= asset('assets/ng/controllers/keu/lovCustomer.min.js') ?>"></script>
@endsection
