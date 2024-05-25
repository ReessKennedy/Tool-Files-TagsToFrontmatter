## What ⚡
Auto converts standard tags to frontmatter tags for an entire directory

## Why 🤷‍♂️
- I had hundreds of notes organized with tags inserted at the top of the note. 
- I started using this method before Obsidian introduced built in frontmatter support 
- Converted all notes to just use frontmatter for page-wide tags seems to make more sense
## How 📋
Disclaimer: Make a full backup of your notes first! - I used this for my own purposes and it worked great running locally on my Mac but I can't test for all circumstances. It really helps to make a backup and then create a clean git repository and run a test of the conversion tool under version control so you can see the changes made in a precise way. Once satisfied and working as you expect then you may run it on your active, live vault. 

Usage: 
1. Alter the value of the `$fileDir` to either your entire vault or a folder within your fault 
2. Execute by running `php Process.php` while in the directory of this repo. 

Suggestion: If you have a big vault with folders it's likely best to just break up the process by folders within your vault instead of running it across your entire vault and commit each set of changes in Git, if you Vault is under version control, just to log all changes by section and make it easier to make sure all went well.
### Screenhots

#### Expected Conversions
![|430](https://drive.google.com/thumbnail?id=1A0OePkJXToTtxThEHXMHs49E5iPSyx15&usp=drive_fs&sz=s4000)

#### Status Response When Running
- ✅ = file was converted
- 🚫 = file was skipped because no conversion needed

![|450](https://drive.google.com/thumbnail?id=1ywCbuXErxhRTHJRJ7WvMlllTNKIRoSgZ&usp=drive_fs&sz=s4000)


## To Do
- Status output just shows file name and should show filename relative to base directory so it's easier to know where this file is in sub directory structure
- Possibility that admonitions may be wrongly identified in some markdown if they are the first thing on a page and start with a hashtag
