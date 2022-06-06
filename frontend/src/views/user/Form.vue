<template>
    <master-layout :title="isEditing ? 'Uredi korisnika' : 'Novi korisnik'" :back-button="true">
        <ion-grid class="page" fixed>
            <ion-row class="ion-justify-content-center">
                <ion-col size="12" size-lg="6">
                    <ion-card>
                        <ion-card-content>
                            <form mode="md" @submit.prevent="submitForm">
                                <button type="submit" v-show="false"></button>

                                <LogoInput v-model="user.logo" :loading="loading" />

                                <ion-item>
                                    <ion-label position="floating">Ime</ion-label>
                                    <ion-input v-model="user.name" :disabled="loading"></ion-input>
                                    <ion-note v-if="v$.name.$errors.length">Ime je obavezno.</ion-note>
                                </ion-item>

                                <ion-item class="gender">
                                    <ion-radio-group v-model="user.gender">
                                        <ion-grid>
                                            <ion-row>
                                                <ion-col size="6">
                                                    <ion-item lines="none">
                                                        <ion-label>Muško</ion-label>
                                                        <ion-radio slot="start" value="M" :disabled="loading"></ion-radio>
                                                    </ion-item>
                                                </ion-col>
                                                <ion-col size="6">
                                                    <ion-item lines="none">
                                                        <ion-label>Žensko</ion-label>
                                                        <ion-radio slot="start" value="F" :disabled="loading"></ion-radio>
                                                    </ion-item>
                                                </ion-col>
                                            </ion-row>
                                        </ion-grid>
                                    </ion-radio-group>
                                </ion-item>

                                <ion-item type="button" @click="setCountryModal(true)" :disabled="loading">
                                    <ion-label position="floating">Država</ion-label>
                                    <ion-input :value="chosenCountryName" readonly></ion-input>
                                    <ion-note v-if="v$.country.$errors.length">Država je obavezna.</ion-note>
                                </ion-item>

                                <ion-item type="button" @click="setClubModal(true)" :disabled="loading">
                                    <ion-label position="floating">Klub</ion-label>
                                    <ion-input :value="chosenClubName" readonly></ion-input>
                                    <ion-note v-if="v$.club_id.$errors.length">Klub je obavezan.</ion-note>
                                </ion-item>
                            </form>
                        </ion-card-content>
                    </ion-card>
                </ion-col>
            </ion-row>
        </ion-grid>

        <ion-modal :is-open="isCountryModalOpen" @didDismiss="setCountryModal(false)" :breakpoints="sheetModalBreakpoints" :initialBreakpoint="sheetModalInitialBreakpoint">
            <ion-page>
                <ion-header>
                    <ion-toolbar>
                        <ion-title>Odaberi državu</ion-title>
                        <ion-buttons slot="end">
                            <ion-button @click="setCountryModal(false)">
                                <ion-icon :icon="closeOutline"></ion-icon>
                            </ion-button>
                        </ion-buttons>
                    </ion-toolbar>
                </ion-header>
                <ion-content>
                    <ion-searchbar placeholder="Traži države..." @ionChange="onCountrySearch"></ion-searchbar>
                    <ion-list>
                        <ion-item v-for="country in countries" :key="country.code" :color="country.code == user.country ? 'primary' : ''" button @click="chooseCountry(country.code)" detail="false">
                            <ion-label>{{ country.name }}</ion-label>
                        </ion-item>
                    </ion-list>
                </ion-content>
            </ion-page>
        </ion-modal>

        <ion-modal :is-open="isClubModalOpen" @didDismiss="setClubModal(false)" :breakpoints="sheetModalBreakpoints" :initialBreakpoint="sheetModalInitialBreakpoint">
            <ion-page>
                <ion-header>
                    <ion-toolbar>
                        <ion-title>Odaberi klub</ion-title>
                        <ion-buttons slot="end">
                            <ion-button @click="setClubModal(false)">
                                <ion-icon :icon="closeOutline"></ion-icon>
                            </ion-button>
                        </ion-buttons>
                    </ion-toolbar>
                </ion-header>
                <ion-content>
                    <ion-searchbar placeholder="Traži klubove..." @ionChange="onClubSearch"></ion-searchbar>
                    <ion-list v-if="clubs.length">
                        <ion-item v-for="club in clubs" :key="club.id" :color="club.id == user.club_id ? 'primary' : ''" button @click="chooseClub(club.id)" detail="false">
                            <ion-label> {{ club.name }}</ion-label>
                        </ion-item>
                    </ion-list>
                </ion-content>
            </ion-page>
        </ion-modal>

        <WideFab @click="submitForm" :loading="loading">Spremi</WideFab>
    </master-layout>
</template>

<script>
import { IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, IonRadioGroup, IonRadio, IonModal, IonPage, IonContent, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonIcon, IonSearchbar, IonList, onIonViewWillEnter, toastController, isPlatform } from '@ionic/vue';
import { closeOutline } from 'ionicons/icons';
import { ref, computed } from 'vue';
import { useRouter, useRoute, onBeforeRouteLeave } from 'vue-router';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { getUser, createUser, updateUser } from '@/http/user';
import { userNotFoundOffline } from '@/helpers/user';
import { getCountries } from '@/helpers/misc';
import { isDesktop } from '@/helpers/misc';
import store from '@/store';
import LogoInput from '@/components/LogoInput';
import WideFab from '@/components/WideFab';

export default {
    name: 'UserFormPage',
    components: { IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, IonRadioGroup, IonRadio, IonModal, IonPage, IonContent, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonIcon, IonSearchbar, IonList, LogoInput, WideFab },
    setup() {
        const router = useRouter();
        const route = useRoute();

        const loading = ref(false);

        const emptyUser = {
            logo: '',
            name: '',
            gender: 'M',
            country: '',
            club_id: ''
        };

        const user = ref({ ...emptyUser });

        // Postavi pravila validacije (ime, država i klub su obavezni).
        const v$ = useVuelidate({
            name: { required },
            country: { required },
            club_id: { required },
        }, user);

        // Ako je ime routea "edit-user" onda uređujemo korisnika. Uzmi ID korisnika iz URLa.
        const isEditing = route.name == 'edit-user';
        const editingId = parseInt(route.params.id);

        // Ako uređujemo korisnika, učitaj njegove podatke sa servera.
        if(isEditing) {
            if(isNaN(editingId)) return userNotFoundOffline();

            const fetchUser = async () => {
                loading.value = true;
                user.value = await getUser(editingId) || await userNotFoundOffline();
                loading.value = false;
            }

            onIonViewWillEnter(() => fetchUser());
        }

        // Učitaj popis svih država.
        const dbCountries = getCountries();

        const countrySearchKeyword = ref();
        const onCountrySearch = (event) => countrySearchKeyword.value = event.detail.value || '';

        // Filtriraj države.
        const countries = computed(() => {
            let countries = dbCountries || [];

            if(countrySearchKeyword.value) {
                countries = countries.filter(country => {
                    return countrySearchKeyword.value.toLowerCase().split(' ').every(v => country.name.toLowerCase().includes(v));
                })
            }

            return countries;
        });

        const sheetModalBreakpoints = !isDesktop() ? [0.4, 1] : undefined;
        const sheetModalInitialBreakpoint = !isDesktop() ? 0.4 : undefined;

        const isCountryModalOpen = ref(false);
        const setCountryModal = (state) => isCountryModalOpen.value = state;

        // Odaberi državu iz modala i zatvori ga.
        const chooseCountry = (country) => {
            user.value.country = country;
            setCountryModal(false);
        }

        const chosenCountryName = computed(() => dbCountries.find((country) => user.value.country == country.code)?.name);

        // Učitaj klubove ako nisu prisutni u storeu.
        const storeClubs = computed(() => store.getters['clubs/clubs']);
        if(!storeClubs.value) {
            loading.value = true;
            store.dispatch('clubs/fetch').then(() => loading.value = false);
        }

        const clubSearchKeyword = ref();
        const onClubSearch = (event) => clubSearchKeyword.value = event.detail.value || '';

        // Filtriraj klubove.
        const clubs = computed(() => {
            let clubs = storeClubs.value || [];

            if(clubSearchKeyword.value) {
                clubs = clubs.filter(club => {
                    return clubSearchKeyword.value.toLowerCase().split(' ').every(v => club.name.toLowerCase().includes(v));
                })
            }

            return clubs;
        });

        const isClubModalOpen = ref(false);
        const setClubModal = (state) => isClubModalOpen.value = state;

        // Odaberi klub iz modala i zatvori ga.
        const chooseClub = (id) => {
            user.value.club_id = id;
            setClubModal(false);
        }

        const chosenClubName = computed(() => storeClubs.value?.find((club) => user.value.club_id == club.id)?.name);
        
        // Pošalji podatke serveru.
        const submitForm = async () => {
            if(!await v$.value.$validate()) return;

            loading.value = true;

            // Dodaj sve ulazne podatke u FormData objekt (koji je potreban jer šaljemo datoteku - logo).
            const formData = new FormData();
            for(const prop in emptyUser) formData.append(prop, user.value[prop] || '');

            let routeId, toastMessage;
            if(isEditing) {
                routeId = editingId;
                toastMessage = 'Korisnik je uspješno uređen.';
                await updateUser(editingId, formData);
            } else {
                routeId = editingId;
                toastMessage = 'Korisnik je uspješno dodan.';
                await createUser(formData).then((created) => routeId = created.id);
            }

            // Prikaži novo dodanog/uređenog korisnika i poruku.
            router.push({ name: 'view-user', params: { id: routeId }});
            toastController.create({ message: toastMessage, duration: 2000 }).then(t => t.present());

            // Osvježi popis svih korisnika.
            store.dispatch('users/fetch');

            // Resetiraj inpute, stanje učitavanja i validaciju inputa.
            user.value = { ...emptyUser };
            loading.value = false;
            v$.value.$reset();
        };

        // Zatvori modale za odabir države/kluba u browseru ako je pritisnut back button (Ionic to automatski rješava na Android aplikaciji).
        onBeforeRouteLeave(async (_to, _from, next) => {
            if((isPlatform('pwa') || isPlatform('mobileweb')) && (isCountryModalOpen.value || isClubModalOpen.value)) {
                isCountryModalOpen.value = false;
                isClubModalOpen.value = false;
                next(false);
            } else {
                next();
            }
        });

        return {
            user,
            isEditing,
            countries,
            chooseCountry,
            onCountrySearch,
            isCountryModalOpen,
            setCountryModal,
            chosenCountryName,
            clubs,
            chooseClub,
            onClubSearch,
            sheetModalBreakpoints,
            sheetModalInitialBreakpoint,
            isClubModalOpen,
            setClubModal,
            chosenClubName,
            submitForm,
            loading,
            v$,
            closeOutline
        };
    }
}
</script>

<style lang="scss" scoped>
@import '@/theme/forms.scss';

ion-card-content {
    ion-item {
        margin-bottom: 16px;
    }

    :last-child {
        margin-bottom: 0;
    }
}

.gender {
    ion-radio-group {
        width: 100%;
        padding: 5.9px 0;
    }

    ion-grid, ion-col {
        padding: 0;
    }

    ion-radio {
        margin: 0 12px 0 0;
    }
}

ion-modal {
    ion-item {
        border-radius: 0;
        --padding-start: 0;
        --padding-end: 0;
        --inner-padding-start: 15px;
        --inner-padding-end: 0;
    }
}
</style>