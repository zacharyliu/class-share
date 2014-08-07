<!DOCTYPE html>
<html>
<body>

<p>Upload a saved HTML file (right-click -> Save as... -> Webpage, HTML Only) of your PowerSchool homepage below. An automated script will parse and import your schedule from this file.</p>

<form action="<?=site_url('import/submit')?>" method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="submit" value="Submit" />
</form>

</body>
</html>