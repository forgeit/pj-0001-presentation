(function () {

	'use strict';

	angular.module('app.tipo-demanda')
		.controller('TipoDemandaForm', TipoDemandaForm);

	TipoDemandaForm.$inject = [
		'controllerUtils',
		'tipoDemandaRest',
		'$scope'];

	function TipoDemandaForm(controllerUtils, dataservice, $scope) {
		/* jshint validthis: true */
		var vm = this;

		vm.atualizar = atualizar;
		vm.tipoDemanda = {};
		vm.editar = false;
		vm.salvar = salvar;
		vm.voltar = voltar;

		iniciar();

		function atualizar(formulario) {
			dataservice.atualizar(vm.tipoDemanda.id_tipo_demanda, vm.tipoDemanda).then(success).catch(error);

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao atualizar o tipo de demanda.');
			}

			function success(response) {
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					voltar();
				}
			}
		}

		function carregar(data) {
			return dataservice.buscar(data).then(success).catch(error);

			function error(response) {
				console.log(response);
				return controllerUtils.promise.criar(false, {});
			}

			function success(response) {
				var tipoDemanda = controllerUtils.getData(response, 'TipoDemandaDto');
				return controllerUtils.promise.criar(true, tipoDemanda);
			}
		}

		function editarObjeto() {
			vm.editar = !angular.equals({}, controllerUtils.$routeParams);
			return !angular.equals({}, controllerUtils.$routeParams);
		}

		function inicializarObjetos(values) {			
			if (editarObjeto()) {
				if (values[0].exec) {
					vm.tipoDemanda = values[0].objeto;
				} else {
					controllerUtils.feed(controllerUtils.messageType.ERROR, 'Não foi possível carregar os tipos de demanda.');
				}
			}
		}

		function iniciar() {
			var promises = [];
			
			if (editarObjeto()) {
				promises.push(carregar(controllerUtils.$routeParams.id));
			}

			return controllerUtils.ready(promises).then(function (values) {
				inicializarObjetos(values);
			});
		}

		function salvar(formulario) {
			if (formulario.$valid) {
				dataservice.salvar(vm.tipoDemanda).then(success).catch(error);
			} else {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Dados inválidos.');
			}

			function error(response) {
				console.log(response);
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao registrar o tipo de demanda.');
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
			controllerUtils.$location.path('tipo-demanda');
		}
	}

})();