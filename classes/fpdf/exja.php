<?php
require(USCES_PLUGIN_DIR.'/classes/fpdf/mbfpdf.php');

// EUC-JP->SJIS �Ѵ���ưŪ�˹Ԥʤ碌����� mbfpdf.php ��� $EUC2SJIS ��
// true �˽������뤫�����Τ褦�˼¹Ի��� true �����ꤷ�Ƥ��Ѵ����Ƥ��ޤ���
$GLOBALS['EUC2SJIS'] = true;

$pdf=new MBFPDF();
$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();
$pdf->AddPage();
$pdf->SetFont(GOTHIC,'U',20);
$pdf->Write(10,"MS�����å� �ݻ� 18 C ���� 83 %\n");
$pdf->SetFont(PGOTHIC,'U',20);
$pdf->Write(10,"MSP�����å� �ݻ� 18 C ���� 83 %\n");
$pdf->SetFont(MINCHO,'U',20);
$pdf->Write(10,"MS��ī �ݻ� 18 C ���� 83 %\n");
$pdf->SetFont(PMINCHO,'U',20);
$pdf->Write(10,"MSP��ī �ݻ� 18 C ���� 83 %\n");
$pdf->SetFont(KOZMIN,'U',20);
$pdf->Write(10,"������ī �ݻ� 18 C ���� 83 %\n");
$pdf->Output();
?>
