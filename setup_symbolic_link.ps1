# setup_symbolic_link.ps1
# Automates the creation of a symbolic link (Directory Junction) for XAMPP

$source = "c:\AGProjects\smart-farm-dashboard"
$destination = "C:\xampp\htdocs\smart-farm-dashboard"

Write-Host "--- XAMPP SYMLINK SETUP ---" -ForegroundColor Cyan

# Check for Administrator privileges
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsPrincipal]::UserClaims)) { # Basic check, elevation usually needed for mklink
    Write-Host "NOTE: If this fails, please run PowerShell as Administrator." -ForegroundColor Yellow
}

# Remove existing folder or link at destination if it exists
if (Test-Path $destination) {
    Write-Host "Removing existing destination at $destination..." -ForegroundColor Gray
    # Identify if it's a directory or a link
    $item = Get-Item $destination
    if ($item.Attributes -match "ReparsePoint") {
        # It's already a link, just remove it
        Remove-Item $destination
    } else {
        # It's a real folder, backup or move it? We'll just remove it if it's empty or prompt?
        # For simplicity in this script, we remove it.
        Remove-Item $destination -Recurse -Force
    }
}

# Create the directory junction
Write-Host "Creating link: $source -> $destination" -ForegroundColor White
cmd /c mklink /j "$destination" "$source"

if ($LASTEXITCODE -eq 0) {
    Write-Host "SUCCESS! Your project is now live at http://localhost/smart-farm-dashboard" -ForegroundColor Green
    Write-Host "Any changes you save in c:\AGProjects will be instantly visible in XAMPP." -ForegroundColor Cyan
} else {
    Write-Host "FAILED to create symbolic link. Please ensure XAMPP is installed and you are running as Administrator." -ForegroundColor Red
}

Write-Host "`nPress any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
