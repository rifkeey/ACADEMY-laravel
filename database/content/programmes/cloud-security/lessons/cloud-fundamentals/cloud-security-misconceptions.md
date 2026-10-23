Most cloud security failures start with a belief that sounds reasonable. This lesson takes seven common ones, explains why each is wrong, and gives you the sentence to use when you hear it in a meeting.

## 1. "The provider handles security"

**Reality:** the provider handles security *of* the cloud. Identities, data and configuration are always yours (lesson 3).
**Say:** "Which parts of this service are ours to configure, and who on our side owns them?"

## 2. "The provider is certified, so we're compliant"

Providers publish independent reports and certificates (ISO/IEC 27001, SOC 2 reports and others) covering **their** controls, for **the services and locations in scope**.
**Reality:** these reports are valuable evidence about the provider, but they say nothing about how *you* configured your account. Many reports also list **complementary user entity controls**: things the customer must do for the provider's controls to work.
**Say:** "That covers the provider's side. What evidence do we have for our side?"

## 3. "It's encrypted at rest, so the data is safe"

**Reality:** encryption at rest protects against someone stealing the physical disks. If a storage bucket is public or an identity with access is compromised, the service **decrypts the data for them**, because they are asking through the front door.
**Say:** "Who can call the service and use the key? That's what decides whether the data is safe."

## 4. "It's in a private subnet, so it can't be reached"

**Reality:** network placement is one layer. Many cloud services (storage, databases, serverless APIs) can be reached through the provider's **public API endpoints** with valid credentials, regardless of subnets. And a "private" VM with outbound internet access can still talk to an attacker.
**Say:** "Private from which direction, and through which path? What about the API?"

## 5. "Default settings are secure enough"

**Reality:** defaults have improved a lot (for example, new storage is increasingly private and encrypted by default), but defaults are designed to make services *work*, not to meet *your* requirements. Logging retention, network exposure, key management and backup protection usually need decisions.
**Say:** "Which of our baseline controls does the default meet, and which do we need to set?"

## 6. "We'll add security after the pilot"

**Reality:** pilots become production. The account structure, identity design and logging chosen for the pilot are the ones you will live with.
**Say:** "Let's put the guardrails in place now; the pilot runs faster when nobody has to redo it."

## 7. "MFA means accounts are safe"

**Reality:** MFA greatly reduces password attacks, but stolen session tokens, consent phishing, long-lived access keys and over-powerful identities bypass it, and weaker MFA methods can be phished.
**Say:** "MFA is essential. What else limits damage if an account is taken over anyway?"

:::scenario Practise it
A project manager says: *"The cloud vendor has ISO 27001, the data is encrypted, and the database is in a private subnet, so we can skip the security review."* Use misconceptions 2, 3 and 4 to write a two-sentence reply before opening the answer.
:::

:::reveal A good reply
"The certificate covers the provider's own controls, and encryption at rest and a private subnet don't stop someone with valid credentials reading the data through the database's API. The review checks the parts that are ours: who can access the database, how it is configured and logged, and how we would detect misuse."
:::

:::takeaway
Each misconception confuses one layer of protection with the whole picture. Ask which side of the shared responsibility model a control covers, what path an attacker would actually use, and what limits damage when one control fails.
:::
