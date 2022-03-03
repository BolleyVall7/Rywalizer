import extractError from '@/api/extractError';
import Flexbox from '@/components/Flexbox/Flexbox';
import { FacebookButton, GoogleButton, OrangeButton } from '@/components/Form/Button/Button';
import Input from '@/components/Form/Input/Input';
import Link from '@/components/Link/Link';
import Modal from '@/components/Modal/Modal';
import modalsStore from '@/store/ModalsStore';
import userStore from '@/store/UserStore';
import { AxiosError } from 'axios';
import { observer } from 'mobx-react';
import React, { useState } from 'react';

const LoginModal: React.FC = observer(() => {
    const [loginValue, setLogin] = useState('');
    const [passwordValue, setPassword] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string>('');

    const login = async () => {
        setIsLoading(true);

        try {
            await userStore.login(loginValue, passwordValue);
            modalsStore.setIsLoginEnabled(false);
        } catch (err) {
            setError(extractError(err as AxiosError).message);
        } finally {
            setIsLoading(false);
        }
    };
    
    return (
        <Modal
            title="Zaloguj się"
            isOpen={modalsStore.isLoginEnabled}
            onClose={() => modalsStore.setIsLoginEnabled(false)}
            width="400px"
            isLoading={isLoading}
            footerItems={[
                <Link key="1" onClick={() => modalsStore.setIsRegisterEnabled(true)}>Zarejestruj się</Link>,
                <OrangeButton key="2" onClick={login}>Zaloguj się</OrangeButton>
            ]}
        >
            <Flexbox flexDirection="column" gap="10px">
                <FacebookButton
                    key="1"
                    onClick={() => {
                        window.location.href = '/api/v1/auth/facebook/redirect';
                    }}
                >Zaloguj się przez Facebooka</FacebookButton>
                <GoogleButton
                    key="2"
                    style={{ marginTop: '5px' }}
                    onClick={() => {
                        window.location.href = '/api/v1/auth/google/redirect';
                    }}
                >Zaloguj się przez Google</GoogleButton>
                <div style={{
                    textAlign: 'center',
                    marginTop: '10px',
                    marginBottom: '-10px'
                }}>
                    lub
                </div>
                <Input label="Login" value={loginValue} onChange={(v) => setLogin(v)} />
                <Input 
                    label="Hasło" 
                    type="password" 
                    value={passwordValue} 
                    onChange={(v) => setPassword(v)}
                    onEnter={() => login()}
                />
                {error && <div style={{fontWeight: 'bold', color: 'red'}}>{error}</div>}
                <div style={{ fontSize: '12px', color: '#a1a1a1', textAlign: 'right' }}>
                    <Link fixedColor onClick={() => modalsStore.setIsRemindPasswordEnabled(true)}>Nie pamiętasz hasła?</Link>
                </div>
            </Flexbox>
        </Modal>
    );
});

export default LoginModal;