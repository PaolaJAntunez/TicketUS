<#
    Respaldo frecuente de la base de datos de TicketUS.

    Vuelca SOLO "ticket_system" (no todo el servidor, a diferencia del backup
    global de Laragon) a C:\laragon\backup\ticket_system\, con timestamp, y
    borra los respaldos de mas de 3 dias para no llenar el disco. Pensado
    para correr cada 15 min via el Programador de tareas de Windows (ver
    scripts/register-backup-task.ps1).

    Motivo: un "php artisan test" con config cacheada corrio migrate:fresh
    contra esta base de datos real y borro todo; el unico respaldo disponible
    tenia ~5 minutos de antiguedad por pura suerte (el backup global de
    Laragon). Esto acorta la ventana de perdida posible a como mucho 15 min,
    y aisla el respaldo a esta base de datos especificamente.
#>

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $projectRoot '.env'

$envVars = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*([A-Z_]+)\s*=\s*(.*?)\s*$') {
        $envVars[$matches[1]] = $matches[2].Trim('"')
    }
}

$dbHost = $envVars['DB_HOST']
$dbPort = $envVars['DB_PORT']
$dbName = $envVars['DB_DATABASE']
$dbUser = $envVars['DB_USERNAME']
$dbPass = $envVars['DB_PASSWORD']

$mysqldump = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe'
$backupDir = 'C:\laragon\backup\ticket_system'

if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

$timestamp = Get-Date -Format 'yyyy-MM-dd_HHmmss'
$outFile = Join-Path $backupDir "ticket_system_$timestamp.sql"

$mysqldumpArgs = @('-h', $dbHost, '-P', $dbPort, '-u', $dbUser)
if ($dbPass) {
    $mysqldumpArgs += "-p$dbPass"
}
$mysqldumpArgs += @('--single-transaction', '--routines', '--triggers', '--no-tablespaces', "--result-file=$outFile", $dbName)

& $mysqldump @mysqldumpArgs

if ($LASTEXITCODE -ne 0) {
    if (Test-Path $outFile) { Remove-Item $outFile -Force }
    Write-Error "mysqldump fallo con codigo $LASTEXITCODE"
    exit 1
}

if ((Get-Item $outFile).Length -eq 0) {
    Remove-Item $outFile -Force
    Write-Error "mysqldump genero un archivo vacio; revisa credenciales/conexion."
    exit 1
}

# Retencion: conserva solo los respaldos de los ultimos 3 dias (a cada 15 min
# son ~288 archivos, ~1.5MB c/u -> unos 430MB en el peor caso).
Get-ChildItem $backupDir -Filter 'ticket_system_*.sql' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-3) } |
    Remove-Item -Force
