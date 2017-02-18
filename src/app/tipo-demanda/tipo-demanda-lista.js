(function () {
	'use strict';

	angular 
		.module('app.tipo-demanda')
		.controller('TipoDemandaLista', TipoDemandaLista);

	TipoDemandaLista.$inject = [
		'$scope', 
		'tipoDemandaRest', 
		'tabelaUtils', 
		'controllerUtils'];

	function TipoDemandaLista($scope, dataservice, tabelaUtils, controllerUtils) {
		/* jshint validthis: true */
		var vm = this;
		vm.tabela = {};
		vm.instancia = {};

		console.log('Teste');

		iniciar();

		function iniciar() {
			montarTabela();
		}

		function montarTabela() {
			criarOpcoesTabela();

			function carregarObjeto(aData) {
				controllerUtils.$location.path('nova-tipo-demanda/' + aData.id_tipo_demanda);
				$scope.$apply();
			}

			function criarColunasTabela() {
				vm.tabela.colunas = tabelaUtils.criarColunas([
					['descricao', 'Descrição'], 
					['id_tipo_demanda', 'Ações', tabelaUtils.criarBotaoPadrao]
				]);
			}

			function criarOpcoesTabela() {
				vm.tabela.opcoes = tabelaUtils.criarTabela(ajax, vm, remover, 'data', carregarObjeto);
				criarColunasTabela();

				function ajax(data, callback, settings) {
					dataservice.buscarTodos(tabelaUtils.criarParametrosGet(data)).then(success).catch(error);

					function error(response) {
						controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao carregar a lista.');
					}

					function success(response) {
						callback(controllerUtils.getData(response, 'datatables'));
					}
				}
			}

			function remover(aData) {
				dataservice.remover(aData.id_tipo_demanda).then(success).catch(error);

				function error(response) {
					controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao remover.');
				}

				function success(response) {
					controllerUtils.feedMessage(response);
					if (response.data.status == 'true') {
						tabelaUtils.recarregarDados(vm.instancia);
					}
				}
			}
		}
	}
})();