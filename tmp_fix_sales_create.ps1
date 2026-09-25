$path = "resources/views/admin/sales/create.blade.php"
$content = Get-Content -Path $path -Raw
$old = @'
                @if(!empty($selectedProducts) && count($selectedProducts))
                    <hr />
                    <h5>Scanned Items</h5>
                    <div id="scanned-list" class="mb-3">
'@
$new = @'
                @if(!empty($selectedProducts) && count($selectedProducts))
'@
if ($content -like "*$old*") {
    $content = $content -replace [regex]::Escape($old), $new
    Set-Content -Path $path -Value $content
    Write-Host "REPLACED"
} else {
    Write-Host "OLD_BLOCK_NOT_FOUND"
}
