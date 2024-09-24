#### **Introduction**

This documentation is specifically for **Code Maintainers** and explains how to set up automatic GPG-signed commits in GitHub Actions, as well as configuring a Personal Access Token (PAT2). The goal is to ensure that automated commits, such as those updating the copyright year, are securely signed with GPG and pushed using a valid PAT2 for authentication. This document is **not** intended for developers or consumers but for maintainers handling automated processes.

---

#### **Steps to Set Up GPG for Automatic Commit Signing**

##### **1. Creating a GPG Key**
First, generate a GPG key that will be used to sign your automated commits.

1. **Generate a new GPG key:**
   Run the following command in your terminal:
      
      ```console
      gpg --full-generate-key
      ```

      Follow the prompts to create your key. It’s recommended to use RSA with a key size of at least 4096 bits.

2. **Export your public key:**
   After generating your key(key does not expire), export your public GPG key using:

      ```console
      gpg --armor --export YOUR_KEY_ID
      ```

   Replace `YOUR_KEY_ID` with the actual ID of your key, which you can get from `gpg --list-keys`.

---

##### **2. Adding the GPG Key to Your GitHub Account**

To allow GitHub to recognize your signed commits, add your public GPG key to your GitHub account.

1. Go to your [GitHub GPG Keys settings](https://github.com/settings/keys).
2. Click on **New GPG Key**.
3. Paste your public GPG key and save it.

---

##### **3. Storing Your GPG Key in GitHub Secrets**

Next, you’ll need to store your private GPG key as a secret in GitHub to allow signing in GitHub Actions.

1. **Export your private key:**
   Run this command to export your private GPG key:

      ```console
      gpg --armor --export-secret-keys YOUR_KEY_ID
      ```

2. **Store the private key in GitHub Secrets:**
      - Go to your repository’s **Settings**.
      - Navigate to **Secrets and variables** → **[Actions](https://github.com/datamweb/sms-rocket/settings/secrets/actions)**.
      - Add a new secret named `GPG_PRIVATE_KEY` and paste the private key.

---

##### **4. Setting Up a Passphrase for GPG Key in GitHub Actions**

If your GPG key is protected by a passphrase, you will also need to securely provide this passphrase to GitHub Actions. Here’s how to do it:

1. **Exporting Your GPG Key with a Passphrase**

    When generating your GPG key, you might have set a passphrase to add an extra layer of security. To automate the commit signing process, you need to store this passphrase securely in GitHub Secrets.



2. **Storing the Passphrase in GitHub Secrets**

      1. **Find your passphrase:**
         When you created your GPG key, you set a passphrase. Ensure you remember it or have it stored securely.

      2. **Add the passphrase to GitHub Secrets:**
         - Go to your repository’s **Settings**.
         - Navigate to **Secrets and variables** → **[Actions](https://github.com/datamweb/sms-rocket/settings/secrets/actions)**.
         - Add a new secret named `PASSPHRASE` and store your passphrase there.


!!! note

      - **Security:** Never expose your private GPG key or PAT2 in your repository. Always use GitHub Secrets to store sensitive information securely.
      - **Token Expiration:** PATs can expire or be revoked. Ensure that your PAT is valid and rotate tokens as necessary.
      - **Testing:** Before applying these changes to your main repository, test them in a separate environment to ensure everything works correctly.