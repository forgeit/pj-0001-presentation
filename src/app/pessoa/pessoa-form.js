(function () {

	'use strict';

	angular.module('app.pessoa')
		.controller('PessoaForm', PessoaForm);

	PessoaForm.$inject = [
		'controllerUtils',
		'pessoaRest',
		'pessoaDto',
		'$scope',
		'logradouroRest'];

	function PessoaForm(controllerUtils, dataservice, dto, $scope, logradouroRest) {
		/* jshint validthis: true */
		var vm = this;

		vm.atualizar = atualizar;
		vm.carregarBairroList = carregarBairroList;
		vm.carregarLogradouroList = carregarLogradouroList;
		vm.pessoa = {};
		vm.editar = false;
		vm.editar = false;
		vm.cidadeList = [];
		vm.filtrar = null;
		vm.filtrarLogradouro = filtrarLogradouro;
		vm.tipoPessoaList = [];
		vm.salvar = salvar;
		vm.voltar = voltar;
		vm.bairroList = [];
		vm.logradouroList = [];
		vm.abrirModal = abrirModal;
		vm.salvarLogradouro = salvarLogradouro;
		vm.logradouro = {};

		vm.teste = teste;

		iniciar();

		function teste() {
			console.log(vm.pessoa);
		}

		function salvarLogradouro(formulario) {
			var objeto = {
				nome: vm.logradouro.nome,
				bairro: vm.pessoa.bairro
			};

			if (formulario.$valid) {
				logradouroRest.salvar(objeto).then(success).catch(error);
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Dados inválidos.');
			}

			function error(response) {
				vm.logradouro = {};
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao registrar o logradouro.');
			}

			function success(response) {
				vm.logradouro = {};
				controllerUtils.feedMessage(response);
				$('#modalLogradouro').modal('hide');

				if (response.data.status == 'true') {
					carregarLogradouroList(vm.pessoa.bairro);
				}
			}

		}

		function abrirModal() {
			vm.logradouro = {};

			if (vm.pessoa.cidade) {
				if (vm.pessoa.bairro) {
					$('#modalLogradouro').modal('show');
				} else {
					controllerUtils.feed(controllerUtils.messageType.WARNING, 'Para registrar um logradouro, é necessário selecionar o bairro.');	
				}
			} else {
				controllerUtils.feed(controllerUtils.messageType.WARNING, 'Para registrar um logradouro, é necessário selecionar a cidade.');	
			}
		}

		function atualizar(formulario) {
			dataservice.atualizar(vm.pessoa.id_pessoa, dto.criarAtualizar(vm.pessoa)).then(success).catch(error);

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao atualizar a pessoa.');
			}

			function success(response) {
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					voltar();
				}
			}
		}

		function carregarPessoa(data) {
			return dataservice.buscar(data).then(success).catch(error);

			function error(response) {
				console.log(response);
				return controllerUtils.promise.criar(false, {});
			}

			function success(response) {
				var departamento = controllerUtils.getData(response, 'PessoaDto');
				var retorno = dto.criarCarregar(departamento);


				$scope.$watch('vm.cidadeList', function() {
					if (vm.cidadeList.length > 0) {
						angular.forEach(vm.cidadeList, function (value, index) {
							if (retorno.cidade) {
								if (value.id_cidade === retorno.cidade.id_cidade) {
									vm.carregarBairroList(vm.pessoa.cidade.id_cidade);
								}
							}
						});
					}
				});

				$scope.$watch('vm.bairroList', function() {
					if (vm.bairroList.length > 0) {
						angular.forEach(vm.bairroList, function (value, index) {
							if (retorno.bairro) {
								if (value.id_bairro === retorno.bairro.id_bairro) {
									vm.carregarLogradouroList(vm.pessoa.bairro.id_bairro);
								}
							}
						});
					}
				});


				return controllerUtils.promise.criar(true, retorno);
			}
		}

		function carregarBairroList(cidade) {
			if (cidade === undefined) {
				vm.bairroList = [];
				return false;
			}

			return dataservice.buscarComboBairro(cidade).then(success).catch(error);

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar os bairros.');
				vm.bairroList = [];
				return [];
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				vm.bairroList = array;
				return array;
			}
		}

		function carregarCidadeList() {
			return dataservice.buscarComboCidade().then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function carregarLogradouroList(bairro) {
			if (bairro === undefined) {
				vm.logradouroList = [];
				return false;
			}

			return dataservice.buscarComboLogradouro(bairro).then(success).catch(error);

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar os logradouros.');
				vm.logradouroList = [];
				return [];
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				vm.logradouroList = array;
				return array;
			}
		}

		function carregarTipoPessoaList() {
			return dataservice.buscarComboTipoPessoa().then(success).catch(error);

			function error(response) {
				return controllerUtils.promise.criar(false, []);
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				return controllerUtils.promise.criar(true, array);
			}
		}

		function editarObjeto() {
			vm.editar = !angular.equals({}, controllerUtils.$routeParams);
			return !angular.equals({}, controllerUtils.$routeParams);
		}

		function filtrarLogradouro() {
			if (vm.filtrar) {
				if (vm.pessoa.bairro) {
					logradouroRest.filtrar({filtro: vm.filtrar, bairro: vm.pessoa.bairro}).then(success).catch(error);	
				} else {
					controllerUtils.feed(controllerUtils.messageType.WARNING, 'Selecione um bairro para filtrar os logradouros.');	
				}
			} else {
				controllerUtils.feed(controllerUtils.messageType.WARNING, 'Para filtrar é necessário inserir dados.');	
			}

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao filtrar os logradouros.');	
			}

			function success(response) {
				var array = controllerUtils.getData(response, 'ArrayList');
				
				if (array.length > 0) {
					vm.logradouroList = array;
					controllerUtils.feed(controllerUtils.messageType.INFO, 'O filtro foi aplicado. ' + array.length + ' encontrado(s).');	
				} else {
					controllerUtils.feed(controllerUtils.messageType.WARNING, 'Nenhum registro encontrado para o filtro informado.');	
				}
			}
		}

		function inicializarObjetos(values) {			
			if (values[0].exec) {
				vm.tipoPessoaList = values[0].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar os tipos.');
			}

			if (values[1].exec) {
				vm.cidadeList = values[1].objeto;
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar as cidades.');
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
			var promises = [];

			promises.push(carregarTipoPessoaList());
			promises.push(carregarCidadeList());
			
			if (editarObjeto()) {
				promises.push(carregarPessoa(controllerUtils.$routeParams.id));
			}

			return controllerUtils.ready(promises).then(function (values) {
				vm.pessoa.fgTipoPessoa = "F";
				inicializarObjetos(values);
			});
		}

		function salvar(formulario) {
			if (formulario.$valid) {
				dataservice.salvar(vm.pessoa).then(success).catch(error);
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Dados inválidos.');
			}

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao registrar a pessoa.');
			}

			function success(response) {
				console.log(response);
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					voltar();
				}
			}
		}

		function voltar() {
			controllerUtils.$location.path('pessoa');
		}
	}

})();