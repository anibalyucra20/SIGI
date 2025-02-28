<!-- start page title -->
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Relación de Docentes</h4>
                <button type="button" class="btn btn-primary waves-effect waves-light" data-toggle="modal" data-target=".modal-nuevo">+ Nuevo</button>
                <div class="modal fade modal-nuevo" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header text-center">
                                <h5 class="modal-title h4 " id="myLargeModalLabel">Nuevo Docente</h5>
                                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="col-12">
                                    <form class="form-horizontal">
                                        <div class="form-group row mb-2">
                                            <label for="dni" class="col-3 col-form-label">DNI</label>
                                            <div class="col-9">
                                                <input type="text" class="form-control" id="dni" name="dni">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="apellidos_nombres" class="col-3 col-form-label">Apellidos y Nombres</label>
                                            <div class="col-9">
                                                <input type="text" class="form-control" id="apellidos_nombres" name="apellidos_nombres">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="inputPassword5" class="col-3 col-form-label">Género</label>
                                            <div class="col-9">
                                                <select name="genero" id="genero" class="form-control">
                                                    <option value=""></option>
                                                    <option value="M">M</option>
                                                    <option value="F">F</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="fecha_nac" class="col-3 col-form-label">Fecha de Nacimiento</label>
                                            <div class="col-9">
                                                <input type="date" class="form-control" id="fecha_nac" name="fecha_nac">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="direccion" class="col-3 col-form-label">Dirección </label>
                                            <div class="col-9">
                                                <input type="text" class="form-control" id="direccion" name="direccion">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="correo" class="col-3 col-form-label">Correo Electrónico</label>
                                            <div class="col-9">
                                                <input type="email" class="form-control" id="correo" name="correo">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="telefono" class="col-3 col-form-label">Teléfono </label>
                                            <div class="col-9">
                                                <input type="text" class="form-control" id="telefono" name="telefono">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="discapacidad" class="col-3 col-form-label">Discapacidad </label>
                                            <div class="col-9">
                                                <select name="discapacidad" id="discapacidad" class="form-control">
                                                    <option value=""></option>
                                                    <option value="NO">NO</option>
                                                    <option value="SI">SI</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="id_sede" class="col-3 col-form-label">Sede </label>
                                            <div class="col-9">
                                                <select name="id_sede" id="id_sede" class="form-control">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="id_rol" class="col-3 col-form-label">Cargo </label>
                                            <div class="col-9">
                                                <select name="id_rol" id="id_rol" class="form-control">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label for="id_programa_estudios" class="col-3 col-form-label">Programa de Estudio </label>
                                            <div class="col-9">
                                                <select name="id_programa_estudios" id="id_programa_estudios" class="form-control">
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0 justify-content-end row text-center">
                                            <div class="col-12">
                                                <button type="button" class="btn btn-light waves-effect waves-light" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success waves-effect waves-light">Registrar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br><br>
                <div id="tablas"></div>
                <div id="modals_editar"></div>
                <div id="modals_permisos"></div>
                
            </div>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL;?>src/view/js/functions_usuario.js"></script>
<script>
    listar_docentesOrdenados();
</script>
<!-- end page title -->