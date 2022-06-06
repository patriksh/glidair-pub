import { Network } from '@capacitor/network';
import store from '@/store';

// Napravi sinkronizaciju rezultata sa serverom.
const syncPendingResultUpdates = () => {
    const pendingResultUpdates = store.getters['competitions/pendingResultUpdates'];
    for(const update of pendingResultUpdates) {
        store.dispatch('competitions/updateRounds', update);
    }
}

// Provjeri ima li rezultata koji čekaju upload pri otvaranju aplikacije i pri spajanju na internet.
export const attachOfflineSyncListener = () => {
    store.dispatch('competitions/loadPendingResultUpdates').then(async () => {
        const status = await Network.getStatus();
        const pendingResultUpdates = store.getters['competitions/pendingResultUpdates'];

        if(status.connected && pendingResultUpdates.length) {
            syncPendingResultUpdates();
        }
    });

    Network.addListener('networkStatusChange', (status) => {
        const pendingResultUpdates = store.getters['competitions/pendingResultUpdates'];

        if(status.connected && pendingResultUpdates.length) {
            syncPendingResultUpdates();
        }
    });
}