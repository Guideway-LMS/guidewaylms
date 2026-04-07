<?php
/**
 * GUIDEWAY CUSTOM -  * Template de validação pública de certificados
 */
defined('_JEXEC') or die;
?>
<?php
// GUIDEWAY CUSTOM - 17/03/2026 - Exibe erro de acesso negado ao certificado
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
if ($erro === 'acesso_negado') {
    echo '<div style="background:#fee2e2;border:1px solid #dc2626;color:#dc2626;padding:15px;margin:20px;border-radius:6px;">
        <strong>Acesso negado.</strong> Você não possui este certificado ou ele não é válido.
    </div>';
}
?>
<div id="splms" class="splms view-validate">
    <div class="container" style="max-width:600px; margin:40px auto; text-align:center;">

        <?php if (empty($this->hash)) : ?>
            <!-- Formulário de busca -->
            <h2>🔍 Validar Certificado</h2>
            <p>Digite o código do certificado para verificar sua autenticidade.</p>
            <form method="get" action="">
                <input type="hidden" name="option" value="com_splms">
                <input type="hidden" name="view" value="validate">
                <input type="text"
                       name="hash"
                       placeholder="Ex: GW2026TEST01"
                       style="padding:10px; width:70%; font-size:14px; border:1px solid #ccc; border-radius:4px;">
                <button type="submit"
                        style="padding:10px 20px; background:#2c3e50; color:white; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
                    Verificar
                </button>
            </form>

        <?php elseif ($this->valido) : ?>
            <!-- Certificado válido -->
            <div style="border:3px solid #27ae60; border-radius:10px; padding:30px; background:#f9fff9;">
                <h2 style="color:#27ae60;">✅ Certificado Válido</h2>
                <p style="font-size:13pt;">Este certificado é autêntico e foi emitido pela Guideway LMS.</p>
                <hr>
                <table style="width:100%; text-align:left; font-size:12pt; margin-top:20px;">
                    <tr>
                        <td><strong>Aluno:</strong></td>
                        <td><?php echo htmlspecialchars($this->certificado->aluno); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Curso:</strong></td>
                        <td><?php echo htmlspecialchars($this->certificado->curso); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Data de emissão:</strong></td>
                        <td>
                            <?php
                            echo (!empty($this->certificado->issue_date) && $this->certificado->issue_date !== '0000-00-00')
                                ? date('d/m/Y', strtotime($this->certificado->issue_date))
                                : 'Não informada';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Código:</strong></td>
                        <td><?php echo htmlspecialchars($this->certificado->certificate_no); ?></td>
                    </tr>
                    <?php if (!empty($this->certificado->instructor)) : ?>
                    <tr>
                        <td><strong>Instrutor:</strong></td>
                        <td><?php echo htmlspecialchars($this->certificado->instructor); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

        <?php else : ?>
            <!-- Certificado inválido -->
            <div style="border:3px solid #e74c3c; border-radius:10px; padding:30px; background:#fff9f9;">
                <h2 style="color:#e74c3c;">❌ Certificado Inválido</h2>
                <p style="font-size:13pt;">Nenhum certificado foi encontrado com o código <strong><?php echo htmlspecialchars($this->hash); ?></strong>.</p>
                <p>Verifique se o código foi digitado corretamente.</p>
                <a href="?option=com_splms&view=validate"
                   style="display:inline-block; margin-top:15px; padding:10px 20px; background:#2c3e50; color:white; border-radius:4px; text-decoration:none;">
                    Tentar novamente
                </a>
            </div>

        <?php endif; ?>

    </div>
</div>
