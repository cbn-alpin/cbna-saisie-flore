$(document).ready(function () {

  $('#photo').fileinput({
    showUpload: false,
    allowedFileExtensions: ['jpg', 'gif', 'png', 'jpeg', 'zip', 'xls', 'csv', 'doc', 'docx', 'xlsx'],
    language: 'fr',
    maxFilesNum: 5,
    maxFileSize: 2048
  });

});
