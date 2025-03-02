import express from 'express';
import { DLMM, DLMMPosition, Wallet } from '@meteora-ag/dlmm-sdk';
import { Connection } from '@solana/web3.js';

const app = express();
const port = 3000;
const SOLANA_RPC = 'https://api.mainnet-beta.solana.com';
const connection = new Connection(SOLANA_RPC);

app.get('/positions/:wallet', async (req, res) => {
  try {
    const walletAddress = req.params.wallet;
    const dlmm = await DLMM.create(connection);
    
    // Buscar todas as posições do usuário
    const positions = await dlmm.getUserPositions(new Wallet(walletAddress));
    
    // Calcular totais
    const totals = positions.reduce((acc, position) => ({
      tokenA: acc.tokenA + position.getTokenAAmount().toNumber(),
      tokenB: acc.tokenB + position.getTokenBAmount().toNumber()
    }), { tokenA: 0, tokenB: 0 });

    res.json({
      positions: positions.map(position => ({
        pool: position.poolAddress.toString(),
        mintA: position.tokenMintA.toString(),
        mintB: position.tokenMintB.toString(),
        liquidity: position.liquidity.toString(),
        tokenA: position.getTokenAAmount().toString(),
        tokenB: position.getTokenBAmount().toString()
      })),
      totals
    });

  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.listen(port, () => {
  console.log(`DLMM Service running on port ${port}`);
});
