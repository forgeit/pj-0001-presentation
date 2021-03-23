(function () {

	'use strict';

	angular.module('app.demanda')
		.controller('DemandaAcompanhamento', DemandaAcompanhamento);

	DemandaAcompanhamento.$inject = [
		'controllerUtils',
		'$scope',
		'FileUploader',
		'configuracaoREST',
		'demandaRest',
		'pessoaRest',
		'situacaoRest'];

	function DemandaAcompanhamento(controllerUtils, $scope, FileUploader, configuracaoREST, demandaRest, pessoaRest, situacaoRest) {
		/* jshint validthis: true */
		var vm = this;

		vm.abrirImagem = abrirImagem;
		vm.validar = validar;
		vm.abrirModalFluxo = abrirModalFluxo;
		vm.habilitarDesabilitar = habilitarDesabilitar;
		vm.uploader = new FileUploader({url: configuracaoREST.url + 'upload'});
		vm.uploader.onSuccessItem = sucessoAoEnviarArquivo;
		vm.salvarFluxo = salvarFluxo;

		iniciar();

		function abrirModalFluxo() {
			$('#modalFluxo').modal('show');
		}

		function habilitarDesabilitar() {
			vm.uploadHabilitado = !vm.uploadHabilitado;
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

		function validar(id, situacao) {
			return demandaRest.validar({ demanda: id, situacao: situacao }).then(success).catch(error);

			function error(response) {
				toastr.error("Erro ao efetuar a validação.");
			}

			function success(response) {
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					iniciar();
				}
			}
		}

		function abrirImagem(objeto, index) {
			vm.imagemAtual = objeto;
			index++;
			vm.nomeArquivo = index.toString().padStart(2, "0");
			$('#modalImagem').modal('show');
		}

		function iniciar() {
			var promises = [];

			promises.push(carregar());
			promises.push(carregarSituacao());

			return controllerUtils.ready(promises).then(function (values) {
				inicializarObjetos(values);
			});	
		}

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

		function inicializarObjetos(values) {
			if (values[0].exec) {
				vm.demanda = values[0].objeto;
				vm.fluxo = [];

				angular.forEach(vm.demanda.fluxo, function (value) {
					value.tsTransacao = new Date(value.tsTransacao);
					vm.fluxo.push(value);
				});

				console.log(vm.fluxo);
				
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao carregar a demanda.');	
			}

			if (values[1].exec) {
				vm.situacaoList = values[1].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Erro ao carregar as fases disponíveis.');	
			}
		}

		function salvarFluxo(formulario) {
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
					iniciar();
					$('#modalFluxo').modal('hide');
				}
			}
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
	}

})();