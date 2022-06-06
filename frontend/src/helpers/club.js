import { toastController } from '@ionic/vue';
import { Network } from '@capacitor/network';
import router from '@/router';

// Vrati korisnika na početnu stranicu i prikaži poruku ako klub nije pronađen.
export const clubNotFound = () => {
    router.push({ name: 'home' });
    toastController.create({ message: 'Klub nije pronađen.', duration: 2000 }).then(t => t.present());

    return null;
}

// Vrati korisnika na početnu stranicu ako pokušava urediti klub dok nema internetske veze.
export const clubNotFoundOffline = async () => {
    router.push({ name: 'home' });

    const message = (await Network.getStatus()).connected ? 'Klub nije pronađen.' : 'Klub nije moguće uređivati dok nema internetske veze.';
    toastController.create({ message, duration: 2000 }).then(t => t.present());

    return null;
}

// Filtriraj ljestvicu poretka klubova (država i spol natjecatelja tog kluba).
export const clubResultFilter = (results, filters) => {
    if(!results) return null;
    if(!filters) return results;

    return results.filter(club => {
        let members = club.members.filter(member => {
            if(filters.country != '' && filters.gender != '') {
                return member.user.country == filters.country && member.user.gender == filters.gender;
            } else if(filters.country != '') {
                return member.user.country == filters.country;
            } else if(filters.gender != '') {
                return member.user.gender == filters.gender;
            } else {
                return member;
            }
        });

        return members.length > 0
    }).sort((a, b) => a.score - b.score);
}