
function showThesisDetails(title, abstract, authors) {
    document.getElementById('modalThesisTitle').textContent = title;
    document.getElementById('modalThesisAuthors').textContent = authors;
    document.getElementById('modalThesisAbstract').textContent = abstract;
    document.getElementById('thesisModal').style.display = 'block';
}
var modal = document.getElementById('thesisModal');
var span = document.getElementsByClassName('close')[0];

span.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
