# PowerShell script to create a proper WordPress plugin zip
$sourceDir = "E:\Mamp\leith\wp-content\plugins\school-finder-pro"
$zipPath = "E:\Mamp\leith\wp-content\plugins\school-finder-pro.zip"

# Remove existing zip
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# Load required assemblies
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

# Create zip file
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)

# Get all files
$files = Get-ChildItem -Path $sourceDir -Recurse -File

foreach ($file in $files) {
    # Get relative path and convert backslashes to forward slashes
    $relativePath = $file.FullName.Substring($sourceDir.Length + 1)
    $entryPath = "school-finder-pro/" + ($relativePath -replace '\\', '/')
    
    # Create entry and copy file
    $entry = $zip.CreateEntry($entryPath)
    $entryStream = $entry.Open()
    $fileStream = [System.IO.File]::OpenRead($file.FullName)
    $fileStream.CopyTo($entryStream)
    $fileStream.Close()
    $entryStream.Close()
}

$zip.Dispose()
Write-Host "Zip created with forward slashes: $zipPath"
