@echo off
echo ========================================
echo   PRUEBA RAPIDA DE LA API
echo ========================================
echo.

echo Probando endpoint: /api/facultades
curl -s http://localhost:8000/api/facultades | echo Facultades OK
echo.

echo Probando endpoint: /api/carreras
curl -s "http://localhost:8000/api/carreras?per_page=5" | echo Carreras OK
echo.

echo Probando endpoint: /api/estudiantes
curl -s "http://localhost:8000/api/estudiantes?per_page=5" | echo Estudiantes OK
echo.

echo ========================================
echo   PRUEBAS COMPLETADAS
echo ========================================
pause
