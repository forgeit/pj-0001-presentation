(function () {
	'use strict';

	angular
		.module('app.usuario')
		.controller('AlterarSenha', AlterarSenha);

	AlterarSenha.$inject = ['jwtHelper', 'AuthToken', 'controllerUtils', 'usuarioRest'];

	function AlterarSenha(jwtHelper, AuthToken, controllerUtils, dataservice) {
		var vm = this;

		vm.alterar = alterar;
		vm.usuario = {};

		function alterar() {
			if (vm.usuario.confirmacao !== vm.usuario.novaSenha) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'O campo nova senha deve ser igual a confirmação.');
				return false;
			}

			var payload = jwtHelper.decodeToken(AuthToken.ler());
			dataservice.atualizar(payload.id, vm.usuario).then(success).catch(error);

			function error(response) {
				controllerUtils.feed(controllerUtils.messageType.ERROR, 'Ocorreu um erro ao alterar a senha.');
			}

			function success(response) {
				controllerUtils.feedMessage(response);

				if (response.data.status == 'true') {
					controllerUtils.$location.path('/');
				}
			}
		}
	}

})();