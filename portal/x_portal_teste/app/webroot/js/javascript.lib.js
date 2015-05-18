/**
 * Biblioteca Javascript com funções auxiliares e extensão de 
 * Objetos nativos
 * 
 * Rodrigo Salles
 */


 /* ============= OBJETO ARRAY ============= */

/* Adiciona um novo elemento no objeto array */
Array.prototype.addObjectWithValue = function(value) {	
	var count = this.length;
	var newCount = this.push(value);
	if(newCount > count) {
		return true;
	} else {
		console.log('The value "' + value + '" cannot be added to the object');
		return false;
	}
}

/* Remove um objeto do array associado ao valor */
Array.prototype.removeObjectWithValue = function(value) {
	if(this.indexOf(value) != -1) {
		this.splice(this.indexOf(value), 1);
	} else {
		console.log('The value "' + value + '" cannot be found');
		return false;
	}
}

/* Verifica se um valor existe no array */
Array.prototype.valueExists = function(value) {
	if(this.indexOf(value) != -1) {
		return true;
	}
	return false;
}

/* Retorna o índice correspondente ao valor */
Array.prototype.indexForValue = function(value) {
	if(this.indexOf(value) != -1) {
		return this.indexOf(value);
	}
	return false;
}
