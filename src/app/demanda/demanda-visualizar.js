(function () {

	'use strict';

	angular.module('app.demanda')
		.controller('DemandaVisualizar', DemandaVisualizar);

	DemandaVisualizar.$inject = [
		'controllerUtils',
		'$scope',
		'FileUploader',
		'configuracaoREST',
		'demandaRest',
		'pessoaRest',
		'situacaoRest'];

	function DemandaVisualizar(controllerUtils, $scope, FileUploader, configuracaoREST, demandaRest, pessoaRest, situacaoRest) {
		/* jshint validthis: true */
		var vm = this;

		vm.demanda = {};
		vm.descricao = null;
		vm.destinatarioList = [];
		vm.situacaoList = [];
		vm.filtrarPessoa = filtrarPessoa;
		vm.demandaFluxo = {};
		vm.habilitarDesabilitar = habilitarDesabilitar;
		vm.uploader = new FileUploader({url: configuracaoREST.url + 'upload'});
		vm.uploader.onSuccessItem = sucessoAoEnviarArquivo;
		vm.salvar = salvar;
		vm.setarDescricao = setarDescricao;
		vm.setarArquivos = setarArquivos;
		vm.voltar = voltar;

		vm.teste = function () {
			console.log(vm.demandaFluxo);
		}

		iniciar();

		function carregar() {
			return demandaRest.buscar(controllerUtils.$routeParams.id).then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'DemandaDto');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function carregarDestinatarios() {
			return pessoaRest.buscarCombo().then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function carregarSituacao() {
			return situacaoRest.buscarCombo().then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function carregarArquivosDemanda(id) {
			return demandaRest.buscarArquivosPorDemandaFluxo(id).then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				vm.arquivos = array;
				return controllerUtils.promise.criar(true, array);
			}
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
					vm.destinatarioList = array;
					controllerUtils.feed(controllerUtils.messageType.INFO, 'O filtro foi aplicado. ' + array.length + ' encontrado(s).');	
				} else {
					controllerUtils.feed(controllerUtils.messageType.WARNING, 'Nenhum registro encontrado para o filtro informado.');	
				}
			}
		}

		function habilitarDesabilitar() {
			vm.uploadHabilitado = !vm.uploadHabilitado;
		}

		function inicializar(values) {
			if (values[0].exec) {
				vm.demanda = values[0].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao carregar a demanda.');	
			}
		}

		function inicializarObjetos(values) {
			if (values[0].exec) {
				vm.demanda = values[0].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao carregar a demanda.');	
			}

			if (values[1].exec) {
				vm.destinatarioList = values[1].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao carregar os destinatários.');	
			}

			if (values[2].exec) {
				vm.situacaoList = values[2].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao carregar as situações');	
			}
		}

		function iniciar() {
			var promises = [];

			promises.push(carregar());
			promises.push(carregarDestinatarios());
			promises.push(carregarSituacao());

			return controllerUtils.ready(promises).then(function (values) {
				inicializarObjetos(values);
			});	
		}

		function salvar(formulario) {

			var possuiArquivoPendente = false;

			if (vm.uploader.queue.length == 0) {
				demandaRest.salvarFluxo(vm.demandaFluxo, controllerUtils.$routeParams.id).then(success).catch(error);
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
							        demandaRest.salvarFluxo(vm.demandaFluxo, controllerUtils.$routeParams.id).then(success).catch(error);
							    },
						        confirmButtonClass: "btn-danger btn-flat",
						        cancelButtonClass: "btn-default btn-flat",
							    confirmButton: "Sim, registrar sem os arquivos!",
							    cancelButton: "Não, aguardar o envio",
							    dialogClass: "modal-dialog modal-lg"
							});
						} else {
							demandaRest.salvarFluxo(vm.demandaFluxo, controllerUtils.$routeParams.id).then(success).catch(error);
						}
					}
				});
			}			

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao registrar o fluxo da demanda.');
			}

			function success(response) {
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					vm.demandaFluxo = {};
					vm.uploader.clearQueue();
					controllerUtils.ready([carregar()]).then(function (values) {
						inicializar(values);
					});	
				}
			}
		}

		function setarArquivos(id) {
			carregarArquivosDemanda(id);
			$('.modalArquivos').modal('show');
		}

		function setarDescricao(descricao) {
			vm.descricao = descricao;
			$('.modalDescricao').modal('show');
		}

		function sucessoAoEnviarArquivo(fileItem, response, status, headers) {
        	if (response.exec == true) {
        		if (!vm.demandaFluxo.arquivos) {
        			vm.demandaFluxo.arquivos = [];
        			vm.demandaFluxo.arquivos.push(response.nome);
        		} else {
        			vm.demandaFluxo.arquivos.push(response.nome);
        		}

        		fileItem.isError = false;
        		fileItem.isCancel = false;
        	} else {
        		fileItem.isError = true;
        		fileItem.isSuccess = false;
        		fileItem.isCancel = false;
        	}
        }

		function voltar() {
			controllerUtils.$location.path('demanda');
		}
	}

})();