var baseBet = 1; var baseMultiplier = 1.01;var variableBase = false;var streakSecurity = 20; var maximumBet = 999999;
var baseSatoshi = baseBet * 100; var currentBet = baseSatoshi;var currentMultiplier = baseMultiplier;var currentGameID = -1;
var firstGame = true;var lossStreak = 0;var coolingDown = false;
console.log('=====WIN=====');
console.log('My username is: ' + engine.getUsername());
console.log('Starting balance: ' + (engine.getBalance() / 100).toFixed(2) + ' bits');
var startingBalance = engine.getBalance();if (variableBase) {
      console.warn('[WARN] Variable mode is enabled and not fully tested. Bot is resillient to ' + streakSecurity + '-loss streaks.');
}
engine.on('game_starting', function(info) {
      console.log('====== New Game ======');console.log('[Bot] Game #' + info.game_id);currentGameID = info.game_id;
      if (coolingDown) {    
      if (lossStreak == 0) {
      coolingDown = false;
      }
      else {
      lossStreak--;
      console.log('[Bot] Cooling down! Games remaining: ' + lossStreak);return;
      }
      }
      if (!firstGame) { // Display data only after first game played.
      console.log('[Stats] Session profit: ' + ((engine.getBalance() - startingBalance) / 100).toFixed(2) + ' bits');
      console.log('[Stats] Profit percentage: ' + (((engine.getBalance() / startingBalance) - 1) * 100).toFixed(2) + '%');
      }
      if (engine.lastGamePlay() == 'LOST' && !firstGame) { 
      lossStreak++;var totalLosses = 0; var lastLoss = currentBet; while (lastLoss >= baseSatoshi) { 
      totalLosses += lastLoss;lastLoss /= 4;
      }
      if (lossStreak > streakSecurity) { 
      coolingDown = true;return;
      }
      currentBet *= 6; currentMultiplier = 1.00 + (totalLosses / currentBet);
      }
      else { 
      lossStreak = 0; if (variableBase) { 
      var divider = 100;for (i = 0; i < streakSecurity; i++) {
      divider += (100 * Math.pow(4, (i + 1)));
      }
      newBaseBet = Math.min(Math.max(1, Math.floor(engine.getBalance() / divider)), maximumBet * 100); 
      newBaseSatoshi = newBaseBet * 100;if ((newBaseBet != baseBet) || (newBaseBet == 1)) {
      console.log('[Bot] Variable mode has changed base bet to: ' + newBaseBet + ' bits');
      baseBet = newBaseBet;baseSatoshi = newBaseSatoshi;
      }
      }
      currentBet = baseSatoshi; currentMultiplier = baseMultiplier;
      }
      console.log('[Bot] Betting ' + (currentBet / 100) + ' bits, cashing out at ' + currentMultiplier + 'x');
      firstGame = false;if (currentBet <= engine.getBalance()) { 
      if (currentBet > (maximumBet * 100)) { 
      console.warn('[Warn] Bet size exceeds maximum bet, lowering bet to ' + (maximumBet * 100) + ' bits');currentBet = maximumBet;
      }
      engine.placeBet(currentBet, Math.round(currentMultiplier * 100), false);
      }
      else { 
      if (engine.getBalance() < 100) {
      console.error('[Bot] Insufficent funds to do anything... stopping');engine.stop();
      }
      else {
      console.warn('[Bot] Insufficent funds to bet ' + (currentBet / 100) + ' bits.');console.warn('[Bot] Resetting to 1 bit basebet'); baseBet = 1;baseSatoshi = 100;
      }
      }
});
engine.on('game_started', function(data) {
    if (!firstGame) { console.log('[Bot] Game #' + currentGameID + ' has started!'); }
});
engine.on('cashed_out', function(data) {
    if (data.username == engine.getUsername()) {      
      console.log('[Bot] Successfully cashed out at ' + (data.stopped_at / 100) + 'x');
      }
});
engine.on('game_crash', function(data) {
    if (!firstGame) { console.log('[Bot] Game crashed at ' + (data.game_crash / 100) + 'x'); }
});