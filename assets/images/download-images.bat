@echo off
cd /d "%~dp0"
for /f "usebackq delims=" %%U in ("IMAGE_SOURCES.txt") do (
  echo Downloading %%~nxU...
  curl -L --fail --retry 3 "%%U" -o "%%~nxU"
)
echo Done.
pause
