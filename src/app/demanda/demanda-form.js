(function () {

	'use strict';

	angular.module('app.demanda')
		.controller('DemandaForm', DemandaForm);

	DemandaForm.$inject = [
		'controllerUtils',
		'pessoaRest',
		'demandaRest',
		'$scope',
		'tipoDemandaRest',
		'datepicker',
		'datepickerSemLimite',
		'FileUploader',
		'configuracaoREST'];

	function DemandaForm(controllerUtils, pessoaRest, dataservice, $scope, tipoDemandaRest, datepicker, datepickerSemLimite, FileUploader, configuracaoREST) {
		/* jshint validthis: true */
		var vm = this;

		vm.demanda = {};
		vm.editar = false;
		vm.filtrarPessoa = filtrarPessoa;
		vm.habilitarDesabilitar = habilitarDesabilitar;
		vm.salvar = salvar;
		vm.solicitanteList = [];
		vm.tipoDemandaList = [];
		vm.uploader = new FileUploader({url: configuracaoREST.url + 'upload'});
		vm.uploader.onSuccessItem = sucessoAoEnviarArquivo;
		vm.uploadHabilitado = false;
		vm.voltar = voltar;
		
		vm.teste = teste;

		iniciar();

		function teste() {
			console.log(vm.demanda);
		}

		function carregarSolicitantes() {
			return pessoaRest.buscarCombo().then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function carregarTipoDemanda() {
			return tipoDemandaRest.buscarTodos().then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'datatables');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function editarObjeto() {
			vm.editar = !angular.equals({}, controllerUtils.$routeParams);
			return !angular.equals({}, controllerUtils.$routeParams);
		}

		function filtrarPessoa() {
			if (vm.filtrar) {
				pessoaRest.buscarComboFiltro({filtro: vm.filtrar}).then(success).catch(error);
			} else {
				controllerUtils.feed(controllerUtils.messageType.WARNING, 'Para filtrar é necessário inserir dados.');	
			}

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao filtrar os solicitantes.');	
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				
				if (array.length > 0) {
					vm.solicitanteList = array;
					controllerUtils.feed(controllerUtils.messageType.INFO, 'O filtro foi aplicado. ' + array.length + ' encontrado(s).');	
				} else {
					controllerUtils.feed(controllerUtils.messageType.WARNING, 'Nenhum registro encontrado para o filtro informado.');	
				}
			}
		}

		function habilitarDesabilitar() {
			vm.uploadHabilitado = !vm.uploadHabilitado;
		}

		function inicializarObjetos(values) {			
			if (values[0].exec) {
				vm.solicitanteList = values[0].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar as pessoas.');
			}

			if (values[1].exec) {
				vm.tipoDemandaList = values[1].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar os tipos de demanda.');
			}

			if (editarObjeto()) {
				if (values[2].exec) {
					vm.pessoa = values[2].objeto;
				} else {
					controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar os dados da pessoa.');
				}
			}
		}

		function iniciar() {
			$('#dtContato').datepicker(datepicker);
			$('#prazoFinal').datepicker(datepickerSemLimite);

			var promises = [];

			promises.push(carregarSolicitantes());
			promises.push(carregarTipoDemanda());
			
			if (editarObjeto()) {
				promises.push(carregarPessoa(controllerUtils.$routeParams.id));
			}

			return controllerUtils.ready(promises).then(function (values) {
				inicializarObjetos(values);
			});
		}

		function salvar(formulario) {

			var possuiArquivoPendente = false;

			if (vm.uploader.queue.length == 0) {
				dataservice.salvar(vm.demanda).then(success).catch(error);
			} else {
				angular.forEach(vm.uploader.queue, function (value, index) {
					if (!(value.isSuccess || value.isError)) {
						possuiArquivoPendente = true;
					}

					if (index === (vm.uploader.queue.length - 1)) {
						if (possuiArquivoPendente) {
							$.confirm({
							    text: "O formulário possui arquivos que ainda não foram enviados, deseja ignora-los?",
							    title: "Confirmação",
							    confirm: function(button) {
							        dataservice.salvar(vm.demanda).then(success).catch(error);
							    },
						        confirmButtonClass: "btn-danger btn-flat",
						        cancelButtonClass: "btn-default btn-flat",
							    confirmButton: "Sim, registrar sem os arquivos!",
							    cancelButton: "Não, aguardar o envio",
							    dialogClass: "modal-dialog modal-lg"
							});
						} else {
							dataservice.salvar(vm.demanda).then(success).catch(error);
						}
					}
				});
			}			

			function error(response) {
				console.log(response);
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao registrar a demanda.');
			}

			function success(response) {
				console.log(response);
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					voltar();
				}
			}
		}

		function sucessoAoEnviarArquivo(fileItem, response, status, headers) {
        	if (response.exec == true) {
        		if (!vm.demanda.arquivos) {
        			vm.demanda.arquivos = [];
        			vm.demanda.arquivos.push(response.nome);
        		} else {
        			vm.demanda.arquivos.push(response.nome);
        		}

        		fileItem.isError = false;
        		fileItem.isCancel = false;
        	} else {
        		fileItem.isError = true;
        		fileItem.isSuccess = false;
        		fileItem.isCancel = false;
        	}
        };

		function voltar() {
			controllerUtils.$location.path('/demanda');
		}
	}

})();