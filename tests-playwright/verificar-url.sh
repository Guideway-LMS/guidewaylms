#!/bin/bash
echo "=== VERIFICANDO URL ==="
echo "1. Testando com curl..."
curl -k -I "https://localhost/guidewaylms/administrator/" 2>&1 | head -20

echo -e "\n2. Testando com wget..."
wget --no-check-certificate --spider "https://localhost/guidewaylms/administrator/" 2>&1 | head -10

echo -e "\n3. URLs para testar:"
echo "   - https://localhost/guidewaylms/administrator/"
echo "   - https://localhost/guidewaylms/"
echo "   - http://localhost/guidewaylms/administrator/"
echo "   - http://127.0.0.1/guidewaylms/administrator/"

echo -e "\n4. Abra manualmente no navegador e verifique:"
echo "   - A página carrega?"
echo "   - Aparece campo de login?"
echo "   - Há erro de certificado SSL?"
