<?php
// views/entities/list.php
$pageTitle = "Cari Hesaplar";
include __DIR__ . '/../../views/layout/header.php';

require_once __DIR__ . '/../../src/Models/EntityModel.php';
$entityModel = new EntityModel($pdo);
$entities = $entityModel->getAllWithBalances();
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-card-dark rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_balance</span>
                Cari Hesaplar
            </h2>
            <button onclick="window.location.href='<?php echo public_url('entity/add'); ?>'" 
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <span class="material-symbols-outlined">add</span>
                Yeni Cari
            </button>
        </div>

        <?php if (isset($_GET['success']) && isset($_GET['message'])): ?>
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border-l-4 border-green-600 rounded-lg">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <p class="text-sm text-green-700 dark:text-green-300"><?php echo htmlspecialchars($_GET['message']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <?php
        $totalDebt = 0;
        $totalCredit = 0;
        foreach ($entities as $entity) {
            if ($entity['balance'] < 0) {
                $totalDebt += abs($entity['balance']);
            } else {
                $totalCredit += $entity['balance'];
            }
        }
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                <div class="text-sm text-red-600 dark:text-red-400 font-semibold mb-1">Toplam Borç</div>
                <div class="text-2xl font-bold text-red-700 dark:text-red-500"><?php echo number_format($totalDebt, 2); ?> ₺</div>
                <div class="text-xs text-red-500 dark:text-red-400 mt-1">Tedarikçilere borçlarımız</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                <div class="text-sm text-green-600 dark:text-green-400 font-semibold mb-1">Toplam Alacak</div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-500"><?php echo number_format($totalCredit, 2); ?> ₺</div>
                <div class="text-xs text-green-500 dark:text-green-400 mt-1">Müşterilerden alacaklarımız</div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="text-sm text-blue-600 dark:text-blue-400 font-semibold mb-1">Net Durum</div>
                <div class="text-2xl font-bold <?php echo ($totalCredit - $totalDebt) >= 0 ? 'text-green-700 dark:text-green-500' : 'text-red-700 dark:text-red-500'; ?>">
                    <?php echo number_format($totalCredit - $totalDebt, 2); ?> ₺
                </div>
                <div class="text-xs text-blue-500 dark:text-blue-400 mt-1">Alacak - Borç</div>
            </div>
        </div>

        <!-- FAZ 5.2: Borç/Alacak Yaşlandırma Analizi -->
        <div class="mb-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 p-6 rounded-lg border border-purple-200 dark:border-purple-800">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-600">schedule</span>
                Borç/Alacak Yaşlandırma Analizi
            </h3>
            
            <?php
            // Analyze aging of debts and credits
            $agingData = [
                '0-30' => ['debt' => 0, 'credit' => 0, 'count' => 0],
                '31-60' => ['debt' => 0, 'credit' => 0, 'count' => 0],
                '61-90' => ['debt' => 0, 'credit' => 0, 'count' => 0],
                '90+' => ['debt' => 0, 'credit' => 0, 'count' => 0],
            ];
            
            foreach ($entities as $entity) {
                if ($entity['balance'] == 0) continue;
                
                // Get last transaction to determine age
                $stmt = $pdo->prepare("SELECT transaction_date FROM inv_entity_transactions WHERE entity_id = ? ORDER BY transaction_date DESC LIMIT 1");
                $stmt->execute([$entity['id']]);
                $lastTrans = $stmt->fetch();
                
                if ($lastTrans) {
                    $daysSince = (strtotime('now') - strtotime($lastTrans['transaction_date'])) / 86400;
                    
                    if ($daysSince <= 30) {
                        $bucket = '0-30';
                    } elseif ($daysSince <= 60) {
                        $bucket = '31-60';
                    } elseif ($daysSince <= 90) {
                        $bucket = '61-90';
                    } else {
                        $bucket = '90+';
                    }
                    
                    if ($entity['balance'] < 0) {
                        $agingData[$bucket]['debt'] += abs($entity['balance']);
                    } else {
                        $agingData[$bucket]['credit'] += $entity['balance'];
                    }
                    $agingData[$bucket]['count']++;
                }
            }
            
            $maxValue = 0;
            foreach ($agingData as $data) {
                $maxValue = max($maxValue, $data['debt'], $data['credit']);
            }
            ?>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <?php foreach ($agingData as $period => $data): ?>
                <div class="bg-white dark:bg-card-dark p-4 rounded-lg border border-gray-200 dark:border-border-dark">
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3"><?php echo $period; ?> Gün</div>
                    
                    <!-- Debt Bar -->
                    <div class="mb-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-red-600 dark:text-red-400">Borç</span>
                            <span class="text-xs font-mono text-red-600 dark:text-red-400"><?php echo number_format($data['debt'], 0); ?> ₺</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full transition-all" style="width: <?php echo $maxValue > 0 ? ($data['debt'] / $maxValue * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Credit Bar -->
                    <div class="mb-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-green-600 dark:text-green-400">Alacak</span>
                            <span class="text-xs font-mono text-green-600 dark:text-green-400"><?php echo number_format($data['credit'], 0); ?> ₺</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: <?php echo $maxValue > 0 ? ($data['credit'] / $maxValue * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">
                        <?php echo $data['count']; ?> cari
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    <strong>💡 İpucu:</strong> 90+ gün sütunundaki yüksek borçlar, ödeme takibi gerektiren kritik durumlardır.
                </p>
            </div>
        </div>

        <!-- Tabs -->
        <?php
        $generalEntities = array_filter($entities, fn($e) => $e['type'] !== 'staff');
        $staffEntities = array_filter($entities, fn($e) => $e['type'] === 'staff');
        ?>
        
        <div class="flex gap-2 mb-4 border-b dark:border-border-dark">
            <button onclick="switchTab('general')" id="btn-general" 
                    class="px-6 py-3 border-b-2 border-primary text-primary font-bold transition-all">
                Genel Cariler (<?php echo count($generalEntities); ?>)
            </button>
            <button onclick="switchTab('staff')" id="btn-staff" 
                    class="px-6 py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium transition-all">
                Personel (<?php echo count($staffEntities); ?>)
            </button>
        </div>

        <!-- General Entities Table -->
        <div id="tab-general" class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-surface-dark text-sm text-gray-600 dark:text-gray-400 uppercase">
                        <th class="p-3 text-left border-b dark:border-border-dark">Cari Adı</th>
                        <th class="p-3 text-left border-b dark:border-border-dark">Tür</th>
                        <th class="p-3 text-left border-b dark:border-border-dark">VKN/TCKN</th>
                        <th class="p-3 text-right border-b dark:border-border-dark">Bakiye</th>
                        <th class="p-3 text-center border-b dark:border-border-dark">Durum</th>
                        <th class="p-3 text-center border-b dark:border-border-dark">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-gray-200">
                    <?php if (empty($generalEntities)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                <span class="material-symbols-outlined text-4xl mb-2 block">inbox</span>
                                Henüz cari kaydı bulunmuyor
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($generalEntities as $entity): ?>
                        <tr class="border-b dark:border-border-dark hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="p-3">
                                <div class="font-semibold"><?php echo htmlspecialchars($entity['name']); ?></div>
                                <?php if (!empty($entity['email'])): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($entity['email']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo $entity['type'] === 'supplier' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 
                                         ($entity['type'] === 'customer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 
                                          'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'); 
                                ?>">
                                    <?php 
                                        echo $entity['type'] === 'supplier' ? 'Tedarikçi' : 
                                             ($entity['type'] === 'customer' ? 'Müşteri' : 'Her İkisi'); 
                                    ?>
                                </span>
                            </td>
                            <td class="p-3 text-sm font-mono"><?php echo htmlspecialchars($entity['tax_id'] ?? '-'); ?></td>
                            <td class="p-3 text-right">
                                <div class="font-bold font-mono <?php echo $entity['balance'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo number_format($entity['balance'], 2); ?> ₺
                                </div>
                                <?php 
                                    $assetBalances = json_decode($entity['asset_balances'] ?? '[]', true);
                                    if(!empty($assetBalances)):
                                ?>
                                    <div class="flex flex-wrap justify-end gap-1 mt-1">
                                        <?php foreach($assetBalances as $ab): if($ab['asset_type'] == 'TL' || $ab['asset_type'] == 'CREDIT_CARD' || round($ab['amount'], 2) == 0) continue; ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold <?php echo $ab['amount'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?> border border-current opacity-80">
                                                <?php echo number_format($ab['amount'], 2); ?>
                                                <span class="ml-0.5 opacity-60"><?php 
                                                    echo $ab['asset_type'] == 'USD' ? '$' : ($ab['asset_type'] == 'EUR' ? '€' : ($ab['asset_type'] == 'GOLD' ? 'Gr' : $ab['asset_type'])); 
                                                ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <?php if ($entity['balance'] < 0): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Borç</span>
                                <?php elseif ($entity['balance'] > 0): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Alacak</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400">Sıfır</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo public_url('entity/statement?id=' . $entity['id']); ?>" 
                                       class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300" 
                                       title="Ekstre Görüntüle">
                                        <span class="material-symbols-outlined text-xl">receipt_long</span>
                                    </a>
                                    <a href="<?php echo public_url('entity/edit?id=' . $entity['id']); ?>" 
                                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" 
                                       title="Düzenle">
                                        <span class="material-symbols-outlined text-xl">edit</span>
                                    </a>
                                    <?php if($entity['id'] != 1): ?>
                                    <button onclick="deleteEntity(<?php echo $entity['id']; ?>)" 
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" 
                                            title="Sil">
                                        <span class="material-symbols-outlined text-xl text-[20px]">delete</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Staff Entities Table -->
        <div id="tab-staff" class="overflow-x-auto hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-surface-dark text-sm text-gray-600 dark:text-gray-400 uppercase">
                        <th class="p-3 text-left border-b dark:border-border-dark">Personel</th>
                        <th class="p-3 text-left border-b dark:border-border-dark">İletişim</th>
                        <th class="p-3 text-right border-b dark:border-border-dark">Cari Bakiye</th>
                        <th class="p-3 text-center border-b dark:border-border-dark">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-gray-200">
                    <?php if (empty($staffEntities)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                <span class="material-symbols-outlined text-4xl mb-2 block">person_off</span>
                                Henüz personel kaydı bulunmuyor
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staffEntities as $entity): ?>
                        <tr class="border-b dark:border-border-dark hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="p-3">
                                <div class="font-semibold text-primary"><?php echo htmlspecialchars($entity['name']); ?></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Personel</div>
                            </td>
                            <td class="p-3 text-sm">
                                <?php if ($entity['phone']): ?>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">call</span>
                                        <?php echo htmlspecialchars($entity['phone']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($entity['email']): ?>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">mail</span>
                                        <?php echo htmlspecialchars($entity['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="p-3 text-right">
                                <div class="font-bold font-mono <?php echo $entity['balance'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo number_format($entity['balance'], 2); ?> ₺
                                </div>
                                <?php 
                                    $assetBalances = json_decode($entity['asset_balances'] ?? '[]', true);
                                    if(!empty($assetBalances)):
                                ?>
                                    <div class="flex flex-wrap justify-end gap-1 mt-1">
                                        <?php foreach($assetBalances as $ab): if($ab['asset_type'] == 'TL' || $ab['asset_type'] == 'CREDIT_CARD' || round($ab['amount'], 2) == 0) continue; ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold <?php echo $ab['amount'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?> border border-current opacity-80">
                                                <?php echo number_format($ab['amount'], 2); ?>
                                                <span class="ml-0.5 opacity-60"><?php 
                                                    echo $ab['asset_type'] == 'USD' ? '$' : ($ab['asset_type'] == 'EUR' ? '€' : ($ab['asset_type'] == 'GOLD' ? 'Gr' : $ab['asset_type'])); 
                                                ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo public_url('entity/statement?id=' . $entity['id']); ?>" 
                                       class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300" 
                                       title="Ekstre">
                                        <span class="material-symbols-outlined text-xl">receipt_long</span>
                                    </a>
                                    <a href="<?php echo public_url('entity/edit?id=' . $entity['id']); ?>" 
                                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" 
                                       title="Düzenle">
                                        <span class="material-symbols-outlined text-xl">edit</span>
                                    </a>
                                    <button onclick="deleteEntity(<?php echo $entity['id']; ?>)" 
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" 
                                            title="Sil">
                                        <span class="material-symbols-outlined text-xl text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    const generalTab = document.getElementById('tab-general');
    const staffTab = document.getElementById('tab-staff');
    const btnGeneral = document.getElementById('btn-general');
    const btnStaff = document.getElementById('btn-staff');
    
    if (tab === 'general') {
        generalTab.classList.remove('hidden');
        staffTab.classList.add('hidden');
        
        btnGeneral.classList.remove('border-transparent', 'text-gray-500');
        btnGeneral.classList.add('border-primary', 'text-primary');
        
        btnStaff.classList.add('border-transparent', 'text-gray-500');
        btnStaff.classList.remove('border-primary', 'text-primary');
    } else {
        staffTab.classList.remove('hidden');
        generalTab.classList.add('hidden');
        
        btnStaff.classList.remove('border-transparent', 'text-gray-500');
        btnStaff.classList.add('border-primary', 'text-primary');
        
        btnGeneral.classList.add('border-transparent', 'text-gray-500');
        btnGeneral.classList.remove('border-primary', 'text-primary');
    }
}

async function deleteEntity(id) {
    if (!confirm('Bu cari kaydı silmek istediğinize emin misiniz? Cari kartı gizlenecek ancak geçmiş hareketler korunacaktır.')) return;
    
    try {
        const res = await fetch('<?php echo public_url('api/delete-entity'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        
        const result = await res.json();
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert('Bir hata oluştu: ' + e.message);
    }
}
</script>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
