import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

const firebaseConfig = {
    apiKey: "AIzaSyCklbYspORoQxKFxCgsi1pXSjd_KEQC5Zs",
    authDomain: "indiedate-cae98.firebaseapp.com",
    projectId: "indiedate-cae98",
    storageBucket: "indiedate-cae98.firebasestorage.app",
    messagingSenderId: "827108444790",
    appId: "1:827108444790:web:8875b1c238bb4029c12d57",
    measurementId: "G-4BB4V1F4SH",
};

const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

export { app, analytics };
