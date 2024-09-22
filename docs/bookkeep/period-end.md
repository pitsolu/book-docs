Period End 
===

via [adamcarheden/bookkeeper](https://github.com/adamcarheden/bookkeeper)

Prior to generating a period's financial statements you must close that period. Closing a period means:

 - All temporary accounts (income, expenses and changes to equity) must have a zero balance. This usually means a journal entry to transfer the balance to a balance sheet account.
 - No additional journal entries may be made for that period.

You need not provide a closing function. Bookkeeper has a built-in one that does the right thing with it's default accounts. However, the whole point of accounting is a more granular tracking of how money flows through your business so you probably want to provide your own closing function that meets your reporting needs.