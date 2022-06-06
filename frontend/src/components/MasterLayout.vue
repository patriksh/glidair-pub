<template>
    <ion-page>
        <ion-header>
            <ion-toolbar color="primary">
                <ion-buttons slot="start">
                    <ion-back-button v-if="backButton" text=""></ion-back-button>
                </ion-buttons>
                <ion-title>{{ title }}</ion-title>
                <ion-buttons slot="end">
                    <LoginButton />
                    <LogoutButton v-if="$route.meta.main" />
                    <slot name="toolbar-buttons-end"></slot>
                </ion-buttons>
            </ion-toolbar>
            <slot name="header-end"></slot>
        </ion-header>
        <ion-content>
            <slot id="content"></slot>
        </ion-content>
    </ion-page>
</template>

<script>
import { IonPage, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle, IonContent } from '@ionic/vue';
import LoginButton from '@/components/LoginButton';
import LogoutButton from '@/components/LogoutButton';

export default {
    name: 'MasterLayout',
    components: { IonPage, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle, IonContent, LoginButton, LogoutButton },
    props: ['backButton', 'title']
}
</script>