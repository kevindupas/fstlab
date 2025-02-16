# **********************************************************
# 
# RUN IF YOU WANT TO EXPLORE DATA
# USE FACTOSHINY PACKAGE TO ANALYSE DATA
# http://factominer.free.fr/graphs/factoshiny-fr.html
# 
# YOU CAN OPEN DIRECTLY THE MATRIX FILE CREATED BY FST-LAB
# 
# **********************************************************

# ----------------------------------------------------------
#define packages to install
packages <- c('ggplot2', 'FactoMineR', 'shiny', 'FactoInvestigate', 'Factoshiny')

#install all packages that are not already installed
install.packages(setdiff(packages, rownames(installed.packages())))

library(ggplot2)
library(FactoMineR)
library(shiny)
library(FactoInvestigate)
library(Factoshiny)

# Sets the character separating each field. The most common are tabulation "\t", semicolon and comma
separateur = "\t" 

# READ DATA FROM FILE ------------------------------------------------------------
fichier = file.choose()
donnees <- read.table(fichier, header=TRUE, sep=separateur, na.strings="NA", row.names = 1, dec=".", strip.white=TRUE)

# fix col names
donneesNames <- read.table(fichier, nrows=1, sep=separateur)
nb.colonnes <- NCOL(donneesNames)
colnames(donnees) <- donneesNames[, c(2:nb.colonnes)]

# to run MCA change numeric to character
for (i in 1:dim(donnees)[2]){donnees[ ,i]=as.factor(donnees[,i])}

## Analyse MCA factoshiny
MCAshiny(donnees)
